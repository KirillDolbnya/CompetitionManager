<?php

use App\Http\Controllers\VkCallbackController;
use App\Http\Middleware\VerifyVkSecret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/vk/callback', [VkCallbackController::class, 'handle'])->middleware(VerifyVkSecret::class);
