<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\ExchangesController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\TokensController;
use App\Http\Controllers\WorkspacesController;

/**
 * ルート
 */
Route::get('/', function () {
    if (Auth::check()) {
        // ユーザーが認証済みの場合
        // return view('applications');

        // AuthenticationsController のインスタンスを取得
        $controller = app(WorkspacesController::class);
        return $controller->index();

    } else {
        // ユーザーが認証されていない場合
        return view('sign');
    }
})->name('home');

/**
 * Workspace
 */
Route::get('/{workspace}',[WorkspacesController::class,'show'])
    ->where('workspace', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->name('view.workspace')
    ->middleware('auth');


Route::post('/workspaces',[WorkspacesController::class,'store'])
    ->name('create.workspaces')
    ->middleware('auth');


/**
 * Exchange
 */
// Route::get('/{workspace}/exchanges',[ExchangesController::class,'index'])
//     ->where('workspace', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
//     ->name('view.workspace')
//     ->middleware('auth');


// Route::get('/{workspace}/exchanges',[ExchangesController::class,'show'])
//     ->where('workspace', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
//     ->name('view.workspace')
//     ->middleware('auth');

// Route::get('/accounts/{first}',[GroupsController::class,'store'])


Route::get('/logout',[AuthenticationsController::class,'logout'])
    ->name('logout')
    ->middleware('auth');

// Route::get('/settings/{first?}', [ApplicationsController::class, 'console'])->middleware('auth')
//     ->where('first', 'personal')
//     ->name('settings');

// Route::post('/settings/{first?}/{second?}/{third?}', [SettingsController::class, 'console'])->middleware('auth')
//     ->where('first', 'personal');

/**
 * 通常のサインインのリダイレクトは
 * ・ホーム signin -> '/'
 * ・同じドメインの前ページ services -> signin -> services
 * ですが、カスタムサインインではリダイレクト先の指定が可能です。
 * システムの開発規模に応じて柔軟にリダイレクト先を変更することが可能です。
 */

// 一般的なルート (認証後は / へリダイレクト)
// Route::match(['get', 'post'], '/signin', [AuthenticationsController::class, 'signin'])
//     ->name('signin')
//     ->middleware('guest'); // ログイン済みユーザーはアクセス不可にする場合

// カスタムルート
Route::match(['get', 'post'], '/signin', function (Request $request) {
    // AuthenticationsController のインスタンスを取得 (依存性注入を利用)
    $controller = app(AuthenticationsController::class);

    // web.phpで定義した name ではなく、Path(例：'/admin')を書いてください。
    return $controller->signin($request, null);
})->name('signin')
  ->middleware('guest');


/**
 * サインアップルート
 */
Route::match(['get', 'post'], '/signup', [AuthenticationsController::class, 'signup'])
    ->name('signup')
    ->middleware('guest');

/**
 * RESTful
 */
Route::post('/{workspace}/tokens',[TokensController::class,'issue'])
     ->name('issue.token')
     ->middleware('auth');

Route::post('/{workspace}/tokens/amortize',[TokensController::class,'amortize'])
     ->name('amortize.token')
     ->middleware('auth');

Route::post('/{workspace}/tokens/sells',[TokensController::class,'sell'])
     ->name('sell.token')
     ->middleware('auth');

Route::post('/{workspace}/tokens/sells/all',[TokensController::class,'sellAll'])
     ->name('all.sell.token')
     ->middleware('auth');