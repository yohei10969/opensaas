<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Individual;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect; // リダイレクト用
use Illuminate\Support\Facades\Validator; // バリデーション用
use Illuminate\View\View; // Viewの型ヒント用
use Illuminate\Http\RedirectResponse; // RedirectResponseの型ヒント用


class AccountsController extends Controller
{
    /**
     * アカウント一覧表示
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // アカウントをページネーションで取得
        $accounts = Account::with('administrator')->latest()->paginate(15); // 必要に応じて件数を調整

        // 'accounts.index' ビューにデータを渡して表示
        return view('accounts.index', compact('accounts'));
    }

    /**
     * アカウント作成フォーム表示
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        // 管理者選択用の Individual リストを取得 (必要に応じて実装)
        // $individuals = Individual::all();
        // return view('accounts.create', compact('individuals'));

        // 'accounts.create' ビューを表示
        return view('accounts.create');
    }

    /**
     * 新規アカウント保存処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // バリデーションルール定義
        $validator = Validator::make($request->all(), [
            'alphabet' => 'nullable|string|max:100',
            'japanese' => 'nullable|string|max:100',
            // 'administrator' => 'required|uuid|exists:individuals,uuid', // individualsテーブルのuuidが存在するかチェック
        ]);

        // バリデーション失敗時
        if ($validator->fails()) {
            return Redirect::back()
                        ->withErrors($validator) // エラーメッセージをセッションに格納
                        ->withInput(); // 入力値をセッションに格納
        }

        // バリデーション済みデータを取得
        $validatedData = $validator->validated();

        /**
         * 
         * 認証済みユーザーのUUIDを取得
         * getAuthenticatedUserUuid は Controller.php に記載
         * 
         */
        $authenticatedUserUuid = $this->getAuthenticatedUserUuid();

        // UUIDが取得できなかった場合（非認証状態など）のエラーハンドリング
        if (!$authenticatedUserUuid) {
            // 適切なエラー処理を行う (例: ログインページへリダイレクト)
            // return Redirect::route('login')->with('error', 'アカウントを作成するにはログインが必要です。');

            // もしくは、エラーメッセージ付きで前のページに戻る
            return Redirect::back()->with('error', '認証情報が見つかりません。');
        }

        // 取得したUUIDを administrator として設定
        $validatedData['administrator'] = $authenticatedUserUuid;

        // アカウントを作成
        Account::create($validatedData);

        // 一覧ページにリダイレクトし、成功メッセージを表示
        // return Redirect::route('accounts.index')->with('success', 'アカウントが正常に作成されました。');
        return Redirect::route('home')->with('success', 'アカウントが正常に作成されました。');
    }

    /**
     * 特定のアカウント詳細表示
     * ルートモデルバインディングにより、IDに対応するAccountモデルが自動的に注入される
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\View\View
     */
    public function show(Account $account): View
    {
        // 必要に応じて関連データをロード
        $account->load('administrator');

        // 'accounts.show' ビューにアカウントデータを渡して表示
        return view('accounts.show', compact('account'));
    }

    /**
     * アカウント編集フォーム表示
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\View\View
     */
    public function edit(Account $account): View
    {
        // 管理者選択用の Individual リストを取得 (必要に応じて実装)
        // $individuals = Individual::all();
        // return view('accounts.edit', compact('account', 'individuals'));

        // 'accounts.edit' ビューにアカウントデータを渡して表示
        return view('accounts.edit', compact('account'));
    }

    /**
     * アカウント更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Account $account): RedirectResponse
    {
        // バリデーションルール定義
        $validator = Validator::make($request->all(), [
            'alphabet' => 'nullable|string|max:100',
            'japanese' => 'nullable|string|max:100',
            'administrator' => 'required|uuid|exists:individuals,uuid',
        ]);

        // バリデーション失敗時
        if ($validator->fails()) {
            return Redirect::back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // バリデーション済みデータを取得
        $validatedData = $validator->validated();

        // アカウントを更新
        $account->update($validatedData);

        // 詳細ページ（または一覧ページ）にリダイレクトし、成功メッセージを表示
        return Redirect::route('accounts.show', $account) // $account->uuid でも可
                       ->with('success', 'アカウントが正常に更新されました。');
    }

    /**
     * アカウント削除処理
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Account $account): RedirectResponse
    {
        try {
            // アカウントを削除
            $account->delete();

            // 一覧ページにリダイレクトし、成功メッセージを表示
            return Redirect::route('accounts.index')
                           ->with('success', 'アカウントが正常に削除されました。');
        } catch (\Exception $e) {
            // 外部キー制約などで削除できない場合のエラーハンドリング
            // (例: administrator が restrict 制約のため、関連データが存在すると削除できない)
            if ($e->getCode() === '23000') { // SQLSTATE[23000]: Integrity constraint violation
                 return Redirect::back()
                                ->with('error', 'このアカウントは使用中のため削除できません。');
            }
            // その他のDBエラー
            return Redirect::back()
                           ->with('error', 'アカウントの削除中にエラーが発生しました。');
        } catch (\Exception $e) {
            // その他の予期せぬエラー
             return Redirect::back()
                           ->with('error', '予期せぬエラーが発生しました。');
        }
    }
}
