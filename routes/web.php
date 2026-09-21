<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceSaleController;
use Illuminate\Support\Facades\Route;

// Ruta principal
Route::get('/', [HomeController::class, 'index'])->name('inicio');

// Recursos principales
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);
Route::resource('service-categories', ServiceCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);

// Ventas específicas
Route::get('/sales/products', [SaleController::class, 'productsIndex'])->name('sales.products.index');
Route::get('/sales/services', [ServiceSaleController::class, 'index'])->name('sales.services.index');

// Reportes (Colocados antes del resource general para evitar conflictos de URL)
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
Route::get('/reports/sales/pdf', [ReportController::class, 'salesPdf'])->name('reports.sales.pdf');
Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
Route::get('/reports/services/pdf', [ReportController::class, 'servicesPdf'])->name('reports.services.pdf');

// Recursos con comodines al final
Route::resource('service-sales', ServiceSaleController::class)->only(['index', 'store', 'destroy']);
Route::resource('sales', SaleController::class)->only(['store', 'destroy']);