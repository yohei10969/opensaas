<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Individual;
use App\Models\Workspace;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // バリデーションに使用
use Illuminate\Validation\Rule; // ユニーク制約（複合）に使用

class MembersController extends Controller
{
    /**
     * メンバー（関係性）の一覧を表示（任意：フィルタリングなどが必要になることが多い）
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 例: 特定の Individual に紐づくメンバーシップを取得
        if ($request->has('individual_uuid')) {
            $members = Member::where('individuals_uuid', $request->input('individual_uuid'))->with(['account'])->get();
        }
        // 例: 特定の Workspace に紐づくメンバーシップを取得
        // elseif ($request->has('workspace_uuid')) {
        //     $members = Member::where('Workspaces_uuid', $request->input('workspaces_uuid'))->with(['individual'])->get();
        // }
        // 全てのメンバーシップを取得（データ量が多い場合は注意）
        // else {
        //     $members = Member::with(['individual', 'account'])->paginate(15); // 必要に応じてページネーション
        // }

        // APIとしてJSONを返す例
        return response()->json($members);

        // Viewを返す例 (別途 view/members/index.blade.php を作成)
        // return view('members.index', compact('members'));
    }

    /**
     * 新しいメンバー（関係性）を保存
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'individuals_uuid' => [
                'required',
                'uuid',
                'exists:individuals,uuid', // individualsテーブルに存在するUUIDか
                // 複合ユニーク制約: individuals_uuid と accounts_uuid の組み合わせが members テーブルでユニークであること
                Rule::unique('members')->where(function ($query) use ($request) {
                    return $query->where('accounts_uuid', $request->input('accounts_uuid'));
                }),
            ],
            'accounts_uuid' => [
                'required',
                'uuid',
                'exists:accounts,uuid', // accountsテーブルに存在するUUIDか
            ],
        ]);

        if ($validator->fails()) {
            // APIとしてJSONエラーを返す例
            // return response()->json(['errors' => $validator->errors()], 422);

            // Webフォームでリダイレクトする例
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $member = Member::create($validator->validated());

            // 成功時のレスポンス (API)
            // return response()->json($member->load(['individual', 'account']), 201); // 作成されたリソースを返す (リレーションも含む)

            // 成功時のレスポンス (Web)
            return redirect()->route('members.index') // 例: 一覧ページへリダイレクト
                           ->with('success', 'メンバーシップが作成されました。');

        } catch (\Exception $e) {
            // エラーハンドリング (API)
            // Log::error('Member creation failed: ' . $e->getMessage()); // エラーログ
            // return response()->json(['message' => 'メンバーシップの作成に失敗しました。'], 500);

            // エラーハンドリング (Web)
            return redirect()->back()->with('error', 'メンバーシップの作成に失敗しました。')->withInput();
        }
    }

    /**
     * 特定のメンバー（関係性）を削除
     *
     * 注意: 複合主キーのため、通常の方法 (Route Model Binding) での取得は難しい場合があります。
     *       ここではリクエストから両方のUUIDを受け取る想定です。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $individuals_uuid  // ルート定義で {individual} などとして受け取ることも可能
     * @param  string $accounts_uuid     // ルート定義で {account} などとして受け取ることも可能
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, string $individuals_uuid, string $accounts_uuid)
    {
        // ルートパラメータから取得する代わりにリクエストボディから取得する場合
        // $individuals_uuid = $request->input('individuals_uuid');
        // $accounts_uuid = $request->input('accounts_uuid');
        // バリデーションを追加するとより堅牢になります
        // Validator::make(['individuals_uuid' => $individuals_uuid, 'accounts_uuid' => $accounts_uuid], [...])->validate();

        $member = Member::where('individuals_uuid', $individuals_uuid)
                        ->where('accounts_uuid', $accounts_uuid)
                        ->first();

        if (!$member) {
            // API
            return response()->json(['message' => 'メンバーシップが見つかりません。'], 404);
            // Web
            // return redirect()->route('members.index')->with('error', 'メンバーシップが見つかりません。');
        }

        try {
            $member->delete();

            // API
            // return response()->json(null, 204); // No Content

            // Web
            return redirect()->route('members.index')->with('success', 'メンバーシップが削除されました。');

        } catch (\Exception $e) {
            // エラーハンドリング (API)
            // Log::error('Member deletion failed: ' . $e->getMessage());
            // return response()->json(['message' => 'メンバーシップの削除に失敗しました。'], 500);

            // エラーハンドリング (Web)
            return redirect()->route('members.index')->with('error', 'メンバーシップの削除に失敗しました。');
        }
    }

    // --- 注意点 ---
    // * show(Member $member) や update(Request $request, Member $member) は
    //   複合主キーのため、標準のルートモデルバインディングがそのままでは機能しません。
    //   もしこれらのアクションが必要な場合は、destroy のように両方のキーを
    //   ルートパラメータやリクエストから受け取り、手動でモデルを検索する必要があります。
    //
    // * このコントローラーは Member（関係性）自体を主軸にしていますが、
    //   実際のアプリケーションでは IndividualController や AccountController 内で
    //   関連付けを追加・削除するメソッド (例: attach, detach) を実装する方が
    //   自然な場合も多いです。
    //   例: POST /individuals/{individual}/accounts  (AccountをIndividualに関連付ける)
    //       DELETE /individuals/{individual}/accounts/{account} (関連付けを解除する)
}
