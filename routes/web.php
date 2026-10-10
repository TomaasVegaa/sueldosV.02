<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Arca\CatalogViewer;
use App\Livewire\Audit\AuditLogViewer;
use App\Livewire\Employers\EmployerIndex;
use App\Livewire\Workforce\EmploymentIndex;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('employers.index'));

    Route::get('/employers', EmployerIndex::class)->name('employers.index');
    Route::get('/workforce', EmploymentIndex::class)->name('workforce.index');
    Route::get('/agreements', \App\Livewire\Agreements\AgreementIndex::class)->name('agreements.index');
    Route::get('/concepts', \App\Livewire\Concepts\ConceptIndex::class)->name('concepts.index');
    Route::get('/catalogs', CatalogViewer::class)->name('catalogs.index');
    Route::get('/audit', AuditLogViewer::class)->name('audit.index');

    // Payroll Settlement & PDF Routes
    Route::get('/payroll', \App\Livewire\Payroll\PayrollRunIndex::class)->name('payroll.index');
    Route::get('/payroll/payslip/{payslip}/pdf', [\App\Http\Controllers\PayrollPdfController::class, 'streamPayslip'])->name('payroll.payslip.pdf');
    Route::get('/payroll/payslip/{payslip}/download', [\App\Http\Controllers\PayrollPdfController::class, 'downloadPayslip'])->name('payroll.payslip.download');
    Route::get('/payroll/batch/{version}/pdf', [\App\Http\Controllers\PayrollPdfController::class, 'downloadBatch'])->name('payroll.batch.pdf');
});
