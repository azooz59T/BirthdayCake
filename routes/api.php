<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeveloperController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/upload-developers', [DeveloperController::class, 'upload']);
Route::get('/cake-days', [DeveloperController::class, 'getCakeDays']);