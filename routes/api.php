<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::apiResource('/share', UserController::class);
Route::apiResource('/tweet', TweetController::class);
Route::apiResource('/like', LikeController::class);
Route::apiResource('/comment', CommentController::class);

// ログイン関連のAPIルート
Route::post('/login', [LoginController::class, 'login']);
// Route::post('/logout', [LogoutController::class, 'logout']);

