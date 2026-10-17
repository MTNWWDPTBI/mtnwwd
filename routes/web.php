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
    return redirect()->route('dashboard');
});

// Dashboard Utama dengan Tipe-Tipe Mesin Material Design
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Katalog Sparepart
    Route::get('/sparepart/catalog', [SparepartController::class, 'index'])->name('sparepart.catalog');
    Route::get('/api/assemblies/{machine_id}', [SparepartController::class, 'getAssemblies'])->name('api.assemblies');

    // Import Data
    Route::get('/import-data', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import-data', [ImportController::class, 'store'])->name('import.store');

    // Master Data CRUD
    Route::resource('machines', MachineController::class);
    Route::resource('assemblies', AssemblyController::class);
    Route::resource('admin/spareparts', AdminSparepartController::class);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
