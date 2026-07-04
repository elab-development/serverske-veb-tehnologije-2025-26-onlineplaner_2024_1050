<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlannerCategoryController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\PlannerItemController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('planners', PlannerController::class);
    Route::apiResource('planners.categories', PlannerCategoryController::class);
    Route::apiResource('planners.items', PlannerItemController::class);
});
