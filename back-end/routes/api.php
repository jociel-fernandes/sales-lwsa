<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SaleController;


Route::middleware(['auth:sanctum'])->group(function () {


    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user', [UserController::class, 'destroy']);
    
    Route::post('sellers/{seller}/resend-commission-email', [SellerController::class, 'resendCommissionEmail'])->middleware('role:admin');
    Route::apiResource('sellers', SellerController::class)->middleware('role:admin');
    Route::get('sellers/me', [SellerController::class, 'me']);
    Route::apiResource('settings', SettingsController::class)->middleware('role:admin');
    // Sales: accessible to authenticated users; controller authorizes admin or sellers
    Route::apiResource('sales', SaleController::class);
});

// Public endpoint to validate password reset token
Route::post('/password/validate', [\App\Http\Controllers\Auth\PasswordValidationController::class, 'validateToken']);