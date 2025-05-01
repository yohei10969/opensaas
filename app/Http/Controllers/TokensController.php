<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Workspace;
use App\Models\Individual;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect; // リダイレクト用
use Illuminate\Support\Facades\Validator; // バリデーション用
use Illuminate\View\View; // Viewの型ヒント用
use Illuminate\Http\RedirectResponse; // RedirectResponseの型ヒント用

use Ramsey\Uuid\Uuid;



class TokensController extends Controller
{
    private const QUANTITY = 'quantity';

    /**
     * 指定されたワークスペース内で、認証ユーザーが発行し保有しているトークンを指定数量だけランダムに償却します。
     * (売り出し中や取引申請中でないものに限る)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $workspace Workspace UUID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function amortize(Request $request, string $workspace): RedirectResponse
    {
        // --- バリデーション ---
        $validated = $request->validate([
            self::QUANTITY => 'required|integer|min:1', // 償却する数量
        ]);

        // ワークスペースUUIDの存在チェック
        if (!Workspace::where('uuid', $workspace)->exists()) abort(404, '指定されたワークスペースが見つかりません。');

        $quantityToAmortize = $validated[self::QUANTITY];
        $currentUserUuid = $this->getAuthenticatedUserUuid();

        // 認証チェック
        if (!$currentUserUuid) return redirect()->back()->with('error', '認証が必要です。');

        // --- 償却可能なトークンを検索 ---
        $amortizableTokensQuery = Token::where('workspaces_uuid', $workspace)
                                       ->where('holder_uuid', $currentUserUuid) // 自分が保有
                                       ->where('issuer_uuid', $currentUserUuid) // かつ自分が発行
                                       ->whereNull('sold_at')        // 売り出し中でない
                                       ->whereNull('applicant_uuid') // 取引申請中でない
                                       ->whereNull('amortized_at');   // まだ償却されていない

        // --- 在庫確認 ---
        $amortizableCount = $amortizableTokensQuery->count();
        if ($amortizableCount < $quantityToAmortize) {
            return redirect()->back()
                             ->with('error', "償却可能なトークンが不足しています。(現在 {$amortizableCount} 個)")
                             ->withInput(); // 入力値を保持
        }

        // --- 選択して更新 ---
        $tokensToUpdate = $amortizableTokensQuery-->orderBy('issued_at', 'asc')
                                                 ->limit($quantityToAmortize)
                                                 ->pluck('uuid'); // 更新対象のUUIDを取得

        Token::whereIn('uuid', $tokensToUpdate)->update(['amortized_at' => now()]);

        return redirect()->route('view.workspace', ['workspace' => $workspace]) // ワークスペース画面などにリダイレクト
                         ->with('success', "{$quantityToAmortize}個のトークンをランダムに償却しました。");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function issue(Request $request, $workspace)
    {
        // ルートパラメータをリクエストデータにマージしてバリデーション対象に含める
        $request->merge(['workspaces_uuid' => $workspace]);

        // --- バリデーション ---
        $validated = $request->validate([
            'workspaces_uuid' => 'required|uuid|exists:workspaces,uuid',
            self::QUANTITY => 'required|integer|min:1|max:100000', // 1Bまで発行可能
            // 'value' => 'required|integer|in:1000,10000,100000', // 指定された額面のみ許可
            // 'interest_rate' => 'required|numeric|between:0,0.200', // 0% から 20% (0.000 - 0.200)
        ]);

        // --- トークン発行処理 ---
        /*
         * 
         * 認証済みユーザーのUUIDを取得
         * getAuthenticatedUserUuid は /app/Http/Controller/Controller.php に記載
         * 
         */
        $issuer = $this->getAuthenticatedUserUuid();

        /**
         * 現在時刻を取得
         */
        $now = now();

        /**
         * トークンの発行枚数を自由自在にする
         */
        $quantityCount = $validated[self::QUANTITY];
        $tokensToInsert = array(); // トークンの発行

        for ($i=0; $i < $quantityCount; $i++) { 
            $tokensToInsert[] = [
                'uuid' => Uuid::uuid4()->toString(),
                'workspaces_uuid' => $validated['workspaces_uuid'],
                'issuer_uuid' => $issuer,
                'holder_uuid' => $issuer, // 発行時は発行者自身が所有者
                'value' => 1000,
                'interest_rate' => 0.01,

                // 発行日時を統一
                'issued_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 配列に格納したトークンデータを一括でデータベースに挿入
        Token::insert($tokensToInsert);

        return redirect()->route('view.workspace', ['workspace'=>$validated['workspaces_uuid']])
                         ->with('success', 'トークンが正常に発行されました。');
    }

    /**
     * 指定されたワークスペース内で、認証ユーザーが保有するトークンを指定数量だけランダムに売り出し中にします。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $workspace Workspace UUID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sell(Request $request, string $workspace): RedirectResponse
    {
        // --- バリデーション ---
        $validated = $request->validate([
            self::QUANTITY => 'required|integer|min:1', // 売り出す数量
        ]);

        // ワークスペースUUIDの存在チェック (ルートパラメータなので厳密には不要かもしれないが念のため)
        if (!Workspace::where('uuid', $workspace)->exists()) {
             abort(404, '指定されたワークスペースが見つかりません。');
        }

        $quantityToSell = $validated[self::QUANTITY];
        $currentUserUuid = $this->getAuthenticatedUserUuid();

        if (!$currentUserUuid) {
            // 認証されていない場合の処理 (通常はmiddlewareで弾かれるはず)
            return redirect()->back()->with('error', '認証が必要です。');
        }

        // --- 売り出し可能なトークンを検索 ---
        $availableTokensQuery = Token::where('workspaces_uuid', $workspace)
                                     ->where('holder_uuid', $currentUserUuid)
                                     ->whereNull('sold_at')      // まだ売り出されていない
                                     ->whereNull('amortized_at'); // 償却されていない

        // --- 在庫確認 ---
        $availableCount = $availableTokensQuery->count();
        if ($availableCount < $quantityToSell) {
            return redirect()->back()
                             ->with('error', "売り出し可能なトークンが不足しています。(現在 {$availableCount} 個)")
                             ->withInput(); // 入力値を保持
        }

        // --- ランダムに選択して更新 ---
        $tokensToUpdate = $availableTokensQuery->inRandomOrder()
                                               ->limit($quantityToSell)
                                               ->pluck('uuid'); // 更新対象のUUIDを取得

        Token::whereIn('uuid', $tokensToUpdate)->update(['sold_at' => now()]);

        return redirect()->route('view.workspace', ['workspace' => $workspace]) // ワークスペース画面などにリダイレクト
                         ->with('success', "{$quantityToSell}個のトークンをランダムに売り出し中にしました。");
    }

    /**
     * 指定されたワークスペース内で、認証ユーザーが保有する未出品・未償却のトークン全てを売り出し中にします。
     *
     * @param  \Illuminate\Http\Request  $request (将来的な拡張用)
     * @param  string $workspace Workspace UUID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sellAll(Request $request, string $workspace): RedirectResponse
    {
        // ワークスペースUUIDの存在チェック
        if (!Workspace::where('uuid', $workspace)->exists()) {
             abort(404, '指定されたワークスペースが見つかりません。');
        }

        $currentUserUuid = $this->getAuthenticatedUserUuid();

        if (!$currentUserUuid) {
            return redirect()->back()->with('error', '認証が必要です。');
        }

        // --- 売り出し可能なトークンを検索するクエリ ---
        $availableTokensQuery = Token::where('workspaces_uuid', $workspace)
                                     ->where('holder_uuid', $currentUserUuid)
                                     ->whereNull('sold_at')      // まだ売り出されていない
                                     ->whereNull('amortized_at'); // 償却されていない

        // --- 対象トークンを一括更新 ---
        // update() メソッドは更新された行数を返す
        $updatedCount = $availableTokensQuery->update(['sold_at' => now()]);

        if ($updatedCount > 0) {
            $message = "{$updatedCount}個のトークンを売り出し中にしました。";
            $status = 'success';
        } else {
            $message = '売り出し中にできるトークンはありませんでした。';
            $status = 'info'; // または 'warning'
        }

        return redirect()->route('view.workspace', ['workspace' => $workspace])
                         ->with($status, $message);
    }
}
