<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// AI Generation endpoints
Route::prefix('ai')->group(function () {
    Route::post('/generate-poem', [AIController::class, 'generatePoem']);
    Route::post('/categorize', [AIController::class, 'categorizePoem']);
    Route::post('/generate-tags', [AIController::class, 'generateTags']);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Victorian Poems API is running',
        'timestamp' => now()->toIso8601String()
    ]);
});
