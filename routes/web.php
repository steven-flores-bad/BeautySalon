<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceSaleController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Inicio de sesión (solo para quien no ha entrado)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Mantiene viva la sesión mientras el usuario interactúa con la página
Route::get('/sesion/ping', [AuthController::class, 'ping'])->middleware(['auth', 'activo'])->name('session.ping');

// Todo el sistema requiere haber iniciado sesión con un usuario activo
Route::middleware(['auth', 'activo'])->group(function () {

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
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/expenses/pdf', [ReportController::class, 'expensesPdf'])->name('reports.expenses.pdf');

    // Ventas de Productos (index incluido: usa el método index() real del controlador)
    Route::resource('sales', SaleController::class)->only(['index', 'store', 'destroy']);

    // Ventas de Servicios
    Route::get('/service-sales/historial', [ServiceSaleController::class, 'history'])->name('service-sales.history');
    Route::resource('service-sales', ServiceSaleController::class)->only(['index', 'store', 'update', 'destroy']);

    // Caja
    Route::get('/caja', [CashRegisterController::class, 'index'])->name('cash-register.index');
    Route::post('/caja/abrir', [CashRegisterController::class, 'open'])->name('cash-register.open');
    Route::put('/caja/apertura', [CashRegisterController::class, 'update'])->name('cash-register.update');
    Route::post('/caja/cerrar', [CashRegisterController::class, 'close'])->name('cash-register.close');

    // Gastos
    Route::get('/expenses/historial', [ExpenseController::class, 'history'])->name('expenses.history');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);

    // Usuarios del sistema
    Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
});
