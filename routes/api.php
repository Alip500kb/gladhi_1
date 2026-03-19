<?php

use App\Http\Controllers\AuthLogin;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/auth/signup', [AuthLogin::class, 'signup']);
    Route::post('/auth/signin', [AuthLogin::class, 'signin']);
    Route::post('/auth/signout', [AuthLogin::class, 'signout'])->middleware('auth:sanctum');

    //Route untuk users
    Route::get('/admins', [UserController::class, 'admins'])->middleware('auth:sanctum');
    Route::get('/users', [UserController::class, 'users'])->middleware('auth:sanctum');
    Route::get('/users/{username}', [UserController::class, 'userdetail']);
    Route::post('/users', [UserController::class, 'newuser']);
});
