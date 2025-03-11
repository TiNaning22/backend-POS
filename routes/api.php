<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProdukTokoController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionItemController;


// Auth Routes
Route::post('/login', [AuthController::class, 'login']);


// Route untuk Superadmin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

});

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Route untuk Admin
    Route::middleware(['role:admin'])->group(function(){
    //produk
        Route::get('/products', [ProdukController::class, 'index']);
        Route::post('/products', [ProdukController::class, 'store']);
        Route::get('/products/{product}', [ProdukController::class, 'show']);
        Route::put('/products/{product}', [ProdukController::class, 'update']);
        Route::delete('/products/{product}', [ProdukController::class, 'destroy']);

        //kategori
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});
    // Route untuk Kasir
    Route::middleware(['role:kasir'])->group(function () {
        //transaksi per item
        Route::get('/transaction-items', [TransactionItemController::class, 'index']);
        Route::post('/transaction-items', [TransactionItemController::class, 'store']);
        Route::get('/transaction-items/{id}', [TransactionItemController::class, 'show']);
        Route::put('/transaction-items/{id}', [TransactionItemController::class, 'update']);
        Route::delete('/transaction-items/{id}', [TransactionItemController::class, 'destroy']);

        //transaksi
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

        Route::post('/logout', [AuthController::class, 'logout']);

    });
});

// CRUD Produk



// CRUD Transaksi


// CRUD toko
Route::get('/toko', [TokoController::class, 'index']);
Route::post('/toko', [TokoController::class, 'store']);
Route::get('/toko/{toko}', [TokoController::class, 'show']);
Route::put('/toko/{toko}', [TokoController::class, 'update']);
Route::delete('/toko/{toko}', [TokoController::class, 'destroy']);

// CRUD Customer
Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{customer}', [CustomerController::class, 'show']);
Route::put('/customers/{customer}', [CustomerController::class, 'update']);
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

// CRUD User
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);

// CRUD produk pertoko
Route::get('/produk-toko', [ProdukTokoController::class, 'index']);
Route::post('/produk-toko', [ProdukTokoController::class, 'store']);
Route::get('/produk-toko/{produkToko}', [ProdukTokoController::class, 'show']);
Route::put('/produk-toko/{produkToko}', [ProdukTokoController::class, 'update']);
Route::delete('/produk-toko/{produkToko}', [ProdukTokoController::class, 'destroy']);

// CRUD Discount
Route::apiResource('customer-diskon', DiskonController::class);

Route::get('shift', [ShiftController::class, 'index']);
Route::post('shift', [ShiftController::class, 'store']);
Route::get('shift/{shift}', [ShiftController::class, 'show']);
Route::put('shift/{shift}', [ShiftController::class, 'update']);
Route::delete('shift/{shift}', [ShiftController::class, 'destroy']);
