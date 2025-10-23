<?php

use App\Http\Controllers\FollowerController;
use Illuminate\Support\Facades\Route;

    Route::post('/follow/{user}', [FollowerController::class, 'toggleFollow']);
    Route::get('/followers', [FollowerController::class, 'followers']);
    Route::get('/following', [FollowerController::class, 'following']);
