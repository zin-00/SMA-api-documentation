<?php

use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;




    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{post}/like', [LikeController::class, 'toggleLike']);
