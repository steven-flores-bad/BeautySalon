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
Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('service-categories', ServiceCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);

// Reportes (colocados antes de los recursos con comodines para evitar conflictos de URL)
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
Route::get('/reports/sales/pdf', [ReportController::class, 'salesPdf'])->name('reports.sales.pdf');
Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
Route::get('/reports/services/pdf', [ReportController::class, 'servicesPdf'])->name('reports.services.pdf');
Route::get('/reports/employees/pdf', [ReportController::class, 'employeesPdf'])->name('reports.employees.pdf');

// Ventas de Productos
Route::get('/sales', [SaleController::class, 'productsIndex'])->name('sales.products.index');
Route::resource('sales', SaleController::class)->only(['store', 'destroy']);

// Ventas de Servicios
Route::resource('service-sales', ServiceSaleController::class)->only(['index', 'store', 'destroy']);