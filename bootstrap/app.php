<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 認証されていないユーザーのリダイレクト先を 'signin' ルートに設定
        $middleware->redirectGuestsTo(fn (Request $request) => route('signin'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
