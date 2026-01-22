<?php

use App\Http\Controllers\Api\V1\BatchApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Routes are loaded by the RouteServiceProvider.
|
*/

// API v1 Routes
Route::prefix('v1')->group(function () {
    
    // Stock Operations (using TDD-tested Batch Services)
    Route::post('/stock-in', [BatchApiController::class, 'stockIn'])
        ->name('api.v1.stock-in');
    
    Route::post('/stock-out', [BatchApiController::class, 'stockOut'])
        ->name('api.v1.stock-out');
    
    // Inventory Queries
    Route::get('/inventory/batches', [BatchApiController::class, 'getBatches'])
        ->name('api.v1.inventory.batches');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
