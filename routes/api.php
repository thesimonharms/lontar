<?php

use Lontar\Blog\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Public: published posts only
Route::get('/posts', [PostController::class, 'index']);

// Authenticated: full CRUD + publish actions
// These routes expect auth:sanctum middleware to be applied by the consuming app.
// drafts must be registered before /posts/{slug} to avoid being swallowed as a slug.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/posts/drafts', [PostController::class, 'drafts']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{slug}', [PostController::class, 'update']);
    Route::delete('/posts/{slug}', [PostController::class, 'destroy']);
    Route::post('/posts/{slug}/publish', [PostController::class, 'publish']);
    Route::post('/posts/{slug}/unpublish', [PostController::class, 'unpublish']);
});

// Must be after /posts/drafts to avoid swallowing it as a slug
Route::get('/posts/{slug}', [PostController::class, 'show']);
