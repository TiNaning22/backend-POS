<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProdukTokoController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\TransactionItemController;

// Route untuk Superadmin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'index']);
});

// Route untuk Admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
});

// Route untuk Kasir
Route::middleware(['auth:sanctum', 'role:kasir'])->group(function () {
    Route::get('/kasir/dashboard', [KasirController::class, 'index']);
});

// CRUD Produk
Route::apiResource('products', ProdukController::class);


// CRUD Transaksi
Route::apiResource('transactions', TransactionController::class);

// CRUD toko
Route::apiResource('toko', TokoController::class);

// CRUD Customer
Route::apiResource('customers', CustomerController::class);

// CRUD User
Route::apiResource('users', UserController::class);

// Crud kategori
Route::apiResource('categories', CategoryController::class);

// CRUD produk pertoko
Route::apiResource('produk-toko', ProdukTokoController::class);

// CRUD Discount
Route::apiResource('customer-diskon', DiskonController::class);

// CRUD Transaction per Item
Route::apiResource('transaction-items', TransactionItemController::class);