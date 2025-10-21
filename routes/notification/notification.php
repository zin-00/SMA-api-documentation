<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;


Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
