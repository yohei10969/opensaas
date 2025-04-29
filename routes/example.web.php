<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\GroupsController;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', function () {
    if (Auth::check()) {
        // ユーザーが認証済みの場合
        return view('applications');
    } else {
        // ユーザーが認証されていない場合
        return view('sign');
    }
})->name('home');


Route::get('/{first}',[AccountsController::class,''])
    ->where('first', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
    ->name('accounts')
    ->middleware('auth');

Route::post('/accounts',[AccountsController::class,'store'])
    ->name('create.account')
    ->middleware('auth');

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

// Route::post('/personal',[AccountsController::class,'store'])->name('personal');

/**
 * RESTfulっぽい
 */
Route::post('/accounts',[AccountsController::class,'store'])->name('create.account');
// Route::post('/accounts/{first}/groups',[GroupsController::class,'store'])
//      ->where('first', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
//      ->name('create.group');
Route::post('/groups',[GroupsController::class,'store'])
     ->name('create.group');