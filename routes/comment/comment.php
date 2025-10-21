<?php

use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;


    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
