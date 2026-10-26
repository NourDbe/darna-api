<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PropertyController as V1PropertyController;
use App\Http\Controllers\Api\V2\PropertyController as V2PropertyController;

Route::prefix('v1')->group(function () {
    Route::get('/properties', [
        V1PropertyController::class,
        'index',
    ]);

    Route::get('/properties/{property}', [
        V1PropertyController::class,
        'show',
    ]);

    Route::patch('/properties/{property}', [
        V1PropertyController::class,
        'update',
    ])->middleware('auth:sanctum');

    Route::delete('/properties/{property}', [
        V1PropertyController::class,
        'destroy',
    ])->middleware('auth:sanctum');
});

Route::prefix('v2')->group(function () {
    Route::get('/properties', [
        V2PropertyController::class,
        'index',
    ]);

    Route::get('/properties/{property}', [
        V2PropertyController::class,
        'show',
    ]);

    Route::patch('/properties/{property}', [
        V2PropertyController::class,
        'update',
    ])->middleware('auth:sanctum');

    Route::delete('/properties/{property}', [
        V2PropertyController::class,
        'destroy',
    ])->middleware('auth:sanctum');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
