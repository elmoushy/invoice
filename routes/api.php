<?php
use App\Http\Controllers\Case1InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;

// User routes
Route::post('/register', [UserController::class, 'register'])->middleware('admin');
Route::post('/login', [UserController::class, 'login']);
Route::put('/user/{id}', [UserController::class, 'update'])->middleware('admin');
Route::delete('/user/{id}', [UserController::class, 'delete'])->middleware('admin');

// Buyer routes
Route::post('/buyer', [BuyerController::class, 'store']);
Route::get('/buyer/{id}', [BuyerController::class, 'show']);
Route::put('/buyer/{id}', [BuyerController::class, 'update']);
Route::delete('/buyer/{id}', [BuyerController::class, 'destroy']);
Route::get('/buyers', [BuyerController::class, 'index']);

// Seller routes
Route::post('/seller', [SellerController::class, 'store']);
Route::get('/seller/{id}', [SellerController::class, 'show']);
Route::put('/seller/{id}', [SellerController::class, 'update']);
Route::delete('/seller/{id}', [SellerController::class, 'destroy']);
Route::get('/sellers', [SellerController::class, 'index']);


// Invoice routes
Route::post('/invoice', [Case1InvoiceController::class, 'store']);
Route::get('/invoice_case1_index', [Case1InvoiceController::class, 'index']);
Route::get('/invoice/{id}', [Case1InvoiceController::class, 'show']);
Route::put('/invoice/{id}', [Case1InvoiceController::class, 'update']);