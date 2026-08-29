<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}', [ArticleController::class, 'show']);
    Route::patch('/articles/{article}/status', [ArticleController::class, 'updateStatus']);
    Route::patch('/articles/{article}/memo', [ArticleController::class, 'updateMemo']);

    Route::get('/sources', [SourceController::class, 'index']);
    Route::post('/sources', [SourceController::class, 'store']);
    Route::post('/sources/{source}/subscribe', [SourceController::class, 'subscribe']);
    Route::delete('/sources/{source}/subscribe', [SourceController::class, 'unsubscribe']);
});
