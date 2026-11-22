<?php

use CodePulse\Http\Controllers\DashboardController;
use CodePulse\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('codepulse.path', 'codepulse'),
    'middleware' => config('codepulse.middleware', ['web']),
    'as' => 'codepulse.',
], function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::get('/reviews/{id}/status', [ReviewController::class, 'status'])->name('reviews.status');
    Route::post('/reviews/{id}/retry', [ReviewController::class, 'retry'])->name('reviews.retry');
});
