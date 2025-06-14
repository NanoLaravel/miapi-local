<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\ReviewController;

Route::apiResource('places', PlaceController::class);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('images', ImageController::class);
Route::apiResource('reviews', ReviewController::class);

// Rutas adicionales para filtrar lugares por tipo o categoría
Route::get('places/type/{type}', [PlaceController::class, 'byType']);
Route::get('places/category/{category}', [PlaceController::class, 'byCategory']);
