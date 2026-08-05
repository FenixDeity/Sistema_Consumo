<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dispositivos', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('/dispositivos', [DeviceController::class, 'store'])->name('devices.store');
    Route::delete('/dispositivos/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('/consumo', [ConsumptionController::class, 'index'])->name('consumo.index');
    Route::post('/consumo', [ConsumptionController::class, 'store'])->name('consumo.store');
    Route::delete('/consumo/{usageLog}', [ConsumptionController::class, 'destroy'])->name('consumo.destroy');
    Route::post('/consumo/apagon/interruptor', [ConsumptionController::class, 'toggleOutage'])->name('outage.toggle');
    Route::post('/consumo/apagon', [ConsumptionController::class, 'storeOutage'])->name('outage.store');

    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reportes/csv', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/compartir', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/compartir', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/compartir/unirse', [GroupController::class, 'join'])->name('groups.join');
    Route::delete('/compartir/{group}', [GroupController::class, 'leave'])->name('groups.leave');
});
