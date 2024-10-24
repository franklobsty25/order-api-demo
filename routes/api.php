<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->prefix('v1/users')->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->prefix('v1/users')->group(function () {
        Route::get('list', 'list');
        Route::get('me', 'me');
        Route::get('list/count', 'countUsers');
        Route::put('update/{email}', 'update');
        Route::delete('delete/{user}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(CustomerController::class)->prefix('v1/customers')->group(function () {
        Route::get('list', 'index');
        Route::get('{customer}', 'show');
        Route::get('overview/stats', 'customersStats');
        Route::post('create', 'store');
        Route::put('update/{customer}', 'update');
        Route::delete('delete/{customer}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ProductController::class)->prefix('v1/products')->group(function () {
        Route::get('list', 'index');
        Route::get('{product}', 'show');
        Route::get('list/count', 'countProducts');
        Route::post('create', 'store');
        Route::put('update/{product}', 'update');
        Route::delete('delete/{product}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(OrderController::class)->prefix('v1/orders')->group(function () {
        Route::get('list', 'index');
        Route::get('{order}', 'show');
        Route::get('overview/stats', 'orderStats');
        Route::post('create/{customer}', 'store');
        Route::put('update/{order}', 'update');
        Route::delete('delete/{order}', 'destroy');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(OrderDetailController::class)->prefix('v1/order-details')->group(function () {
        Route::get('list', 'index');
        Route::get('{orderDetail}', 'show');
        Route::post('create', 'store');
        Route::put('update/{orderDetail}', 'update');
        Route::delete('delete/{orderDetail}', 'destroy');
    });
});
