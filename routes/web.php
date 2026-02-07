<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetReportController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\EventProjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NotificationController;
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

    Route::prefix('master')->group(function () {
        // Cash Accounts Routes
        Route::resource('cash-accounts', CashAccountController::class);
        // Transaction Categories Routes
        Route::resource('categories', TransactionCategoryController::class);
        // Budget Routes
        Route::resource('budgets', BudgetController::class);
        // Users Routes
        Route::resource('users', UserController::class);
        // Roles Routes
        Route::resource('roles', RoleController::class);
    });

    // Transactions Routes
    Route::resource('transactions', TransactionController::class);

    // Cash Flows Routes
    Route::resource('cash-flows', CashFlowController::class);

    // Reports Route
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');

    Route::resource('event-projects', EventProjectController::class);
    Route::post('/event-projects/{id}/approval', [EventProjectController::class, 'approval'])->name('event-projects.approval');
    Route::post('/event-projects/{id}/generate-cash-flow', [EventProjectController::class, 'generateCashFlow'])->name('event-projects.generate-cash-flow');

    // Budget Reports Routes
    Route::get('/budget-reports', [BudgetReportController::class, 'index'])->name('budget-reports.index');
    Route::get('/budget-reports/{budget}', [BudgetReportController::class, 'show'])->name('budget-reports.show');
    Route::get('/budget-reports/{budget}/pdf', [BudgetReportController::class, 'generatePdf'])->name('budget-reports.pdf');

    // Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notificationId}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__.'/auth.php';
