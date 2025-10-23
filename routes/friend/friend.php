<?php

use App\Http\Controllers\FriendController;
use Illuminate\Support\Facades\Route;

    Route::get('/friends', [FriendController::class, 'listFriends']);
    Route::get('/friends/pending', [FriendController::class, 'listPending']);
    Route::get('/friends/requests', [FriendController::class, 'friendRequests']);

    Route::post('/friends/send', [FriendController::class, 'sendRequest']);
    Route::post('/friends/accept', [FriendController::class, 'acceptRequest']);
    Route::post('/friends/unfriend', [FriendController::class, 'unfriend']);
    Route::post('/friends/block', [FriendController::class, 'block']);
    Route::post('/friends/restrict', [FriendController::class, 'restrict']);
