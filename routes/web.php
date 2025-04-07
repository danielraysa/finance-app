<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetReportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Cash Accounts Routes
    Route::resource('cash-accounts', CashAccountController::class);
    
    // Transaction Categories Routes
    Route::resource('categories', TransactionCategoryController::class);
    
    // Transactions Routes
    Route::resource('transactions', TransactionController::class);
    
    // Reports Route
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    
    // Budget Routes
    Route::resource('budgets', BudgetController::class);
    
    // Budget Reports Routes
    Route::get('/budget-reports', [BudgetReportController::class, 'index'])->name('budget-reports.index');
    Route::get('/budget-reports/{budget}', [BudgetReportController::class, 'show'])->name('budget-reports.show');
    Route::get('/budget-reports/{budget}/pdf', [BudgetReportController::class, 'generatePdf'])->name('budget-reports.pdf');
});

require __DIR__.'/auth.php';
