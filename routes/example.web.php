<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationsController;

/**
 * 
 */
Route::get('/', function () {
    return view('welcome');
});

/**
 * 
 */
Route::get('/logout',[AuthenticationsController::class,'logout'])->name('logout');

Route::get('/signin',[AuthenticationsController::class,'signin'])->name('signin');
Route::post('/signin',[AuthenticationsController::class,'signin']);

Route::get('/signup',[AuthenticationsController::class,'signup'])->name('signup');
Route::post('/signup',[AuthenticationsController::class,'signup']);