<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Ruta principal que apunta al controlador
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);
Route::resource('sales', SaleController::class)->only(['index', 'store', 'destroy']);
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
Route::get('/reports/sales/pdf', [ReportController::class, 'salesPdf'])->name('reports.sales.pdf');