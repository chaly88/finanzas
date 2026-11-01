<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Cuentas\CuentaIndex;
use App\Livewire\Movimientos\MovimientoIndex;
use App\Livewire\Dashboard\DashboardIndex;
use App\Livewire\Presupuestos\PresupuestoIndex;
use App\Livewire\Informes\InformeIndex;
use App\Livewire\Deudas\DeudaIndex;
use App\Livewire\Categorias\CategoriaIndex;
use App\Livewire\Ahorros\MetaIndex;
use App\Livewire\MovimientosRecurrentes\MovimientoRecurrenteIndex;
use App\Livewire\Archivos\ArchivoIndex;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\Auth\LoginController;

Route::view('/login', 'adminlte::auth.login')
    ->middleware('guest')
    ->name('login');
    
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
// Route::get('/', function () {
//     return view('welcome');
// });

// Route::redirect('/', '/dashboard');

// Route::view('/dashboard', 'dashboard')
//     ->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/cuentas', CuentaIndex::class)->name('cuentas.index');

    Route::get('/movimientos', MovimientoIndex::class)
        ->name('movimientos.index');

    Route::get('/', DashboardIndex::class)->name('dashboard');


    Route::get('/presupuestos', PresupuestoIndex::class)
        ->name('presupuestos.index');

    Route::get('/informes', InformeIndex::class)
        ->name('informes.index');

    Route::get('/deudas', DeudaIndex::class)->name('deudas.index');

    Route::get('/categorias', CategoriaIndex::class)
        ->name('categorias.index');

    Route::get('/ahorros', MetaIndex::class)->name('ahorros.index');

    Route::get('/movimientos-recurrentes', MovimientoRecurrenteIndex::class)
        ->name('movimientos-recurrentes.index');

    Route::get('/archivos', ArchivoIndex::class)->name('archivos.index');
    Route::get('/archivos/{archivo}/vista-previa', [ArchivoController::class, 'preview'])
        ->name('archivos.preview');
});
