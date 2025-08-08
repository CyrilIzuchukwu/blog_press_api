<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\InteractionController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::middleware('token')->group(function () {
    // Blog routes
    Route::apiResource('blogs', BlogController::class);

    // Post routes
    Route::prefix('blogs/{blog}')->group(function () {
        Route::apiResource('posts', PostController::class)->except(['show']);
        Route::get('posts/{post}', [PostController::class, 'show']);
    });

    // Interaction routes
    Route::post('posts/{post}/like', [InteractionController::class, 'like']);
    Route::post('posts/{post}/comment', [InteractionController::class, 'comment']);
});
