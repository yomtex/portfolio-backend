<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;

// routes/api.php
Route::get('/test', function () {
    return ['message' => 'API works!'];
});

Route::post('/login',[AuthController::class,'login']);

// Public project list
Route::get('/projects',[ProjectController::class,'index']);
Route::get('/projects/{id}',[ProjectController::class,'show']);
Route::get('/projects/slug/{slug}', [ProjectController::class, 'showBySlug']);

// Protected admin routes (middleware: auth:sanctum)
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/me',[AuthController::class,'me']);
    Route::post('/logout',[AuthController::class,'logout']);

    // Admin project management
    Route::get('/admin/projects',[ProjectController::class,'adminIndex']);
    Route::post('/admin/projects',[ProjectController::class,'store']);
    Route::get('/admin/projects/{id}',[ProjectController::class,'show']);
    Route::post('/admin/projects/{id}',[ProjectController::class,'update']);
    Route::delete('/admin/projects/{id}',[ProjectController::class,'destroy']);

    Route::post('/admin/projects/{id}/delete-image',[ProjectController::class,'deleteImage']);
});
