<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\AdminSparepartController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Halaman Home (Dashboard Utama WWD)
Route::get('/dashboard', function () {
    return view('dashboard-utama'); // Nanti kita buat file ini untuk dashboard general
})->middleware(['auth', 'verified'])->name('dashboard');

// Halaman Khusus Modul Sparepart
Route::get('/sparepart/catalog', [HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('sparepart.catalog');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/import-data', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import-data', [ImportController::class, 'store'])->name('import.store');
    // Halaman Katalog Utama
    Route::get('/sparepart/catalog', [SparepartController::class, 'index'])->name('sparepart.catalog');
    
    // API Dropdown Assembly bergantung pada Mesin
    Route::get('/api/assemblies/{machine_id}', [SparepartController::class, 'getAssemblies']);
    
    // ... route import-data biarkan tetap ada ...
    Route::middleware('auth')->group(function () {
    // ... route sparepart dan import yang sudah ada ...

    // Route untuk Master Mesin
    Route::resource('machines', MachineController::class);
    Route::resource('assemblies', AssemblyController::class);
    Route::resource('admin/spareparts', AdminSparepartController::class);
    });
});
require __DIR__.'/auth.php';
