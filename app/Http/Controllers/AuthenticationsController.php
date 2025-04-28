<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Models\Personal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; // Auth::attempt の失敗時に使用
use Illuminate\Support\Facades\DB; // DB ファサードを追加
use Illuminate\Support\Facades\Log; // Log ファサードを追加 (エラーログ用)

class AuthenticationsController extends Controller
{
    private const EMAIL = 'email';
    private const PASSWORD = 'password';
    
    /**
     * ユーザー登録フォームの表示またはユーザー登録処理
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function signup(Request $request)
    {
        // GETリクエストの場合、サインアップフォームを表示
        if ($request->isMethod('get')) return view('sign', ['route' => route('signup')]);

        /**
         * 
         * 
         * 
         */
        // バリデーション
        $validatedData = $request->validate([
            self::EMAIL => 'required|email|max:255|unique:individuals,email',
            self::PASSWORD => 'required|string|min:8',
            // 'password' => 'required|string|min:3|confirmed', // パスワードの確認(必要になったら)
        ]);

        /***
         * 
         * 
         * individuals には値が入っているが、
         * personalsには値が入っていないというズレをなくすための処理
         * 
         * 
         */
        // DB トランザクション開始
        DB::beginTransaction();

        try {
            // ユーザー登録 (Individual 作成)
            $user = Individual::create($validatedData);

            // Personal レコードを作成 (リレーションを利用)
            Personal::create([
                'individuals_uuid' => $user->uuid,
            ]);

            // トランザクションをコミット
            DB::commit();

            // サインアップ後に自動的にログイン
            Auth::login($user);

            // ログインが成功してる場合リダイレクト
            return redirect()->route('home');

        } catch (\Exception $e) {
            // エラーが発生した場合、トランザクションをロールバック
            DB::rollBack();

            // エラーログを記録 (開発/デバッグ時に役立ちます)
            Log::error('User signup failed: ' . $e->getMessage());

            // ユーザーにエラーを通知
            // ここでは ValidationException を再利用していますが、別のエラーページにリダイレクトしたり、専用のエラーメッセージを表示する方が親切な場合もあります。
            throw ValidationException::withMessages([
                 self::EMAIL => 'ユーザー登録処理中にエラーが発生しました。しばらくしてからもう一度お試しください。',
            ]);
        }
    }



    /**
     * サインインフォームの表示またはサインイン処理
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function signin(Request $request)
    {
        // GETリクエストの場合、サインインフォームを表示
        if ($request->isMethod('get')) return view('sign',['route' => route('signin')]); // ここで'sign'ビューファイルを表示
        
        // POSTリクエストの場合、認証処理
        $credentials = $request->validate([
            self::EMAIL => ['required', 'email'],
            self::PASSWORD => ['required', 'string'],
        ]);

        // Auth::attempt で認証試行 (ユーザー検索、パスワード検証、ログインをまとめて行う)
        if (Auth::attempt($credentials)) {
            // セッションIDを再生成してセッション固定化攻撃を防ぐ
            $request->session()->regenerate();

            // 認証成功後、ホーム画面へリダイレクト
            return redirect()->intended('/'); // intended はログイン前にアクセスしようとしたURLがあればそこへ、なければ トップページ へ
        }

        // 認証失敗時の処理
        throw ValidationException::withMessages([
            self::EMAIL => __('auth.failed'), // 多言語対応ファイルからメッセージ取得
        ]);
    }
    
    
    /**
     * ログアウト処理
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // ログアウト処理
        Auth::logout();
        
        // セッションの破棄
        $request->session()->invalidate();
        
        // セッションIDの再生成
        $request->session()->regenerateToken();
        
        // リダイレクト
        return redirect()->route('signin');
    }
}
