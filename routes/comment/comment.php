<?php

use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;


    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment_id}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy']);
