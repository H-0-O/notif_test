<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/notifications')->group(function(){
    Route::get("/" , [NotificationController::class , 'index']);
    Route::get("/{id}" , [NotificationController::class , 'show']);

    Route::post("retry/{id}" , [NotificationController::class , 'retry']);
});

Route::post('/send', [UserController::class , 'notifyUser']);
Route::post('/deliver', [UserController::class , 'deliver']);
