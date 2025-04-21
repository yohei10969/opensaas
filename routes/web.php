<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * 
 */
// Route::get('/logout',[::class,'logout'])->name('logout');

// Route::get('/signin',[::class,'signin'])->name('signin');
// Route::post('/signin',[::class,'signin']);

// Route::get('/signup',[::class,'signup'])->name('signup');
// Route::post('/signup',[::class,'signup']);

/**
 * 
 */
// Route::get('/signin',[::class,'signin'])->name('');
// Route::post('/signin',[::class,'signin']);

// Route::get('/signup',[::class,'signup'])->name('');
// Route::post('/signup',[::class,'signup']);



/**
 * 
 * 正規表現による一括定義
 * Auth
 * 
 */
// Route::post('accounts/{first?}/{second?}/{third?}', [::class, 'create'])->middleware('auth')
//     ->where('first', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}') 
//     ->where('second', '')
//     ->where('third', '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}')
//     ->name('create');