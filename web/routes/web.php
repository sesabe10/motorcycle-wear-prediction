<?php

use App\Http\Controllers\AnaliticaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NuevaClaveController;
use App\Http\Controllers\Auth\RecuperarClaveController;
use App\Http\Controllers\Auth\RegistroUsuarioController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\MotoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\SimulacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/registrarse', [RegistroUsuarioController::class, 'create'])->name('registrarse');
    Route::post('/registrarse', [RegistroUsuarioController::class, 'store']);

    Route::get('/recuperar', [RecuperarClaveController::class, 'create'])->name('password.request');
    Route::post('/recuperar', [RecuperarClaveController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer/{token}', [NuevaClaveController::class, 'create'])->name('password.reset');
    Route::post('/restablecer', [NuevaClaveController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [PanelController::class, 'inicio'])->name('inicio');
    Route::get('/motos/registrar', [MotoController::class, 'create'])->name('motos.registrar');
    Route::post('/motos', [MotoController::class, 'store'])->name('motos.store');
    Route::get('/motos/{moto}', [PanelController::class, 'show'])->whereNumber('moto')->name('motos.panel');

    Route::post('/motos/{moto}/simular', [SimulacionController::class, 'store'])->whereNumber('moto')->name('motos.simular');
    Route::get('/motos/{moto}/mantenimientos/crear', [MantenimientoController::class, 'create'])->whereNumber('moto')->name('mantenimientos.crear');
    Route::post('/motos/{moto}/mantenimientos', [MantenimientoController::class, 'store'])->whereNumber('moto')->name('mantenimientos.store');

    Route::get('/analitica/mis-motos', [AnaliticaController::class, 'misMotos'])->name('analitica.mis_motos');
    Route::get('/analitica/flota', [AnaliticaController::class, 'flota'])->name('analitica.flota');
});
