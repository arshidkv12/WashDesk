<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobCardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UtilityController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'year' => date('Y'),
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('job-cards', JobCardController::class);
    Route::get('/customers/search', [CustomerController::class, 'search'])
        ->name('customers.search');
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/job-cards', [JobCardController::class, 'jobCardsByCustomer'])
        ->name('customers.job-cards.index');
    Route::post('/upload-job-files', [UploadController::class, 'store'])
        ->name('upload.store');
    Route::delete('/upload-job-files', [UploadController::class, 'destroy'])
        ->name('upload.destroy');
    Route::get('/services/search', [ServiceController::class, 'search'])
        ->name('services.search');
    Route::resource('services', ServiceController::class); 
    Route::resource('invoices', InvoiceController::class); 
    Route::get('/print/{id}',  [UtilityController::class, 'print']);
});

require __DIR__ . '/settings.php';
