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

//認証不要
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('firebase.auth')->group(function () {
    Route::apiResource('/users', UserController::class);
    Route::apiResource('/tweets', TweetController::class);
    Route::apiResource('/likes', LikeController::class);
    Route::apiResource('/comments', CommentController::class);
});

