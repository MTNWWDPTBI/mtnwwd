<?php

use App\Http\Controllers\AdminSparepartController;
use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HandoverController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmartAssistantController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\TPMController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WwdProjectController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard Utama
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // ========================================================
    // ZONA UMUM (BISA DIAKSES OLEH ADMIN & TEKNISI)
    // ========================================================

    // Profile User
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Katalog Sparepart (Hanya untuk pencarian & melihat)
    Route::get('/sparepart/catalog', [SparepartController::class, 'index'])->name('sparepart.catalog');
    Route::get('/api/assemblies/{machine_id}', [SparepartController::class, 'getAssemblies'])->name('api.assemblies');

    // TPM Umum & Scan Barcode
    Route::get('/tpm', [TPMController::class, 'index'])->name('tpm.index');
    Route::get('/tpm/scan', [TPMController::class, 'scan'])->name('tpm.scan');
    Route::post('/tpm/scan/process', [TPMController::class, 'processScan'])->name('tpm.processScan');

    // Shift Handover (Input dan Konfirmasi Problem Mesin)
    Route::get('/handover', [HandoverController::class, 'index'])->name('handover.index');
    Route::get('/handover/create', [HandoverController::class, 'create'])->name('handover.create');
    Route::post('/handover/store', [HandoverController::class, 'store'])->name('handover.store');
    Route::post('/handover/resolve/{id}', [HandoverController::class, 'resolve'])->name('handover.resolve');

    // Rute untuk WWD Projects
    Route::resource('wwd-projects', WwdProjectController::class);

    // Rute update sekaligus (bulk update) semua checklist mesin dalam project
    Route::put('wwd-projects/{id}/bulk-update', [WwdProjectController::class, 'bulkUpdate'])
        ->name('wwd-projects.bulk-update');

    // Rute khusus untuk teknisi update detail checklist JSON
    Route::put('wwd-projects/details/{id}', [WwdProjectController::class, 'updateDetail'])
        ->name('wwd-projects.details.update');

    // Rute Smart Assistant
    Route::get('/smart-assistant', [SmartAssistantController::class, 'index'])->name('assistant.index');
    Route::post('/smart-assistant/chat', [SmartAssistantController::class, 'chat'])->name('assistant.chat');
    Route::post('/smart-assistant/clear', [SmartAssistantController::class, 'clearHistory'])->name('assistant.clear');
    Route::get('/smart-assistant/history', [SmartAssistantController::class, 'getHistory'])->name('assistant.history');

    // ========================================================
    // ZONA KHUSUS ADMIN (DIGEMBOK MIDDLEWARE)
    // ========================================================
    Route::middleware('admin')->group(function () {

        // Import Data Master dari Excel
        Route::get('/import-data', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import-data', [ImportController::class, 'store'])->name('import.store');

        // TAMBAHKAN INI: Rute khusus Import SAP
        Route::post('/import-sap', [ImportController::class, 'importSap'])->name('import.sap');

        // Master Data CRUD (Manajemen Mesin, Assembly, dan Sparepart)
        Route::resource('machines', MachineController::class);
        Route::resource('assemblies', AssemblyController::class);
        Route::resource('admin/spareparts', AdminSparepartController::class);

        // Master Barang TPM (Tambah Baru & Hapus Permanen)
        Route::get('/tpm/create', [TPMController::class, 'create'])->name('tpm.create');
        Route::post('/tpm/store', [TPMController::class, 'store'])->name('tpm.store');
        Route::delete('/tpm/destroy/{id}', [TPMController::class, 'destroy'])->name('tpm.destroy');

        // Manajemen Akun (User Manager)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Export Data
        Route::get('/export/tpm', [ExportController::class, 'exportTpm'])->name('export.tpm');
        Route::get('/export/tpm-transactions', [ExportController::class, 'exportTpmTransactions'])->name('export.tpm.transactions');
        Route::get('/export/handover', [ExportController::class, 'exportHandover'])->name('export.handover');

        // Rute Export Khusus Project WWD (Hanya Admin)
        Route::get('/export/wwd-project/{id}', [ExportController::class, 'exportWwdProject'])->name('export.wwd_project');

        // Jalankan Migrasi Database via Web Browser (Khusus Admin, tanpa perlu SSH / terminal Hostinger)
        Route::get('/admin/run-migrate', function () {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return response("<pre style='background:#0f172a;color:#38bdf8;padding:24px;border-radius:8px;font-family:monospace;font-size:14px;line-height:1.5;'>🚀 <strong>DATABASE MIGRATION COMPLETED</strong><br><br>".e($output).'</pre>');
        })->name('admin.migrate');

    });
});

require __DIR__.'/auth.php';
