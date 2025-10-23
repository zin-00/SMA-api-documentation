<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\FriendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
















Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);



    require __DIR__.'/message/message.php';
    require __DIR__.'/notification/notification.php';
    require __DIR__.'/post/post.php';
    require __DIR__.'/friend/friend.php';
    require __DIR__.'/follow/follow.php';
    require __DIR__.'/comment/comment.php';

});
