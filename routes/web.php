<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeriodTrackingController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportAndSettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Public SaaS Homepage (/ and /welcome)
Route::get('/', function () {
    return view('landing');
})->name('welcome');

Route::get('/welcome', function () {
    return view('landing');
})->name('welcome.alias');

// Authentication, Registration, Pending Approval, Signed Email Approvals & 2FA Challenge Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/pending-approval', [AuthController::class, 'showApprovalNotice'])->name('approval.notice');
Route::get('/email-approval/users/{targetUser}/approve', [AdminUserController::class, 'approveViaEmail'])
    ->middleware('signed')
    ->name('admin.users.email-approve');
Route::get('/email-approval/users/{targetUser}/reject', [AdminUserController::class, 'rejectViaEmail'])
    ->middleware('signed')
    ->name('admin.users.email-reject');
Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge'])->name('two-factor.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // 1. Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2, 3, 4. Transactions, Expenses & Income CRUD
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/expenses', [TransactionController::class, 'expenses'])->name('expenses.index');
    Route::get('/income', [TransactionController::class, 'income'])->name('income.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // 5. Daily, Weekly, Monthly & Yearly Tracking + Spending Breakdown Explorer
    Route::get('/tracking', [PeriodTrackingController::class, 'index'])->name('tracking.index');

    // 5b. Expense & Income Categories + Subcategories
    Route::get('/categories', [PeriodTrackingController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [PeriodTrackingController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [PeriodTrackingController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [PeriodTrackingController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/subcategories', [PeriodTrackingController::class, 'storeSubcategory'])->name('subcategories.store');
    Route::put('/subcategories/{subcategory}', [PeriodTrackingController::class, 'updateSubcategory'])->name('subcategories.update');
    Route::delete('/subcategories/{subcategory}', [PeriodTrackingController::class, 'destroySubcategory'])->name('subcategories.destroy');

    // 6. Budgets
    Route::get('/budgets', [PlanningController::class, 'budgets'])->name('budgets.index');
    Route::post('/budgets/targets', [PlanningController::class, 'updateTargets'])->name('budgets.targets');
    Route::post('/budgets/allocations', [PlanningController::class, 'storeCategoryAllocation'])->name('budgets.allocations.store');
    Route::delete('/budgets/allocations/{allocation}', [PlanningController::class, 'destroyCategoryAllocation'])->name('budgets.allocations.destroy');

    // 7. Recurring Expenses & Reminders
    Route::get('/recurring', [PlanningController::class, 'recurring'])->name('recurring.index');
    Route::post('/recurring', [PlanningController::class, 'storeRecurring'])->name('recurring.store');
    Route::put('/recurring/{recurring}', [PlanningController::class, 'updateRecurring'])->name('recurring.update');
    Route::post('/recurring/{recurring}/process', [PlanningController::class, 'processRecurringNow'])->name('recurring.process');
    Route::delete('/recurring/{recurring}', [PlanningController::class, 'destroyRecurring'])->name('recurring.destroy');

    // 8. Financial Insights & Spending Intelligence
    Route::get('/insights', [PeriodTrackingController::class, 'insights'])->name('insights.index');

    // 9. Reports & Exports (CSV, Excel, PDF)
    Route::get('/reports', [ReportAndSettingsController::class, 'reports'])->name('reports.index');
    Route::get('/reports/export', [ReportAndSettingsController::class, 'export'])->name('reports.export');

    // 10. Savings & Financial Goals
    Route::get('/savings', [PlanningController::class, 'savings'])->name('savings.index');
    Route::post('/savings/goals', [PlanningController::class, 'storeGoal'])->name('savings.goals.store');
    Route::put('/savings/goals/{goal}', [PlanningController::class, 'updateGoal'])->name('savings.goals.update');
    Route::post('/savings/goals/{goal}/archive', [PlanningController::class, 'toggleArchiveGoal'])->name('savings.goals.archive');
    Route::delete('/savings/goals/{goal}', [PlanningController::class, 'destroyGoal'])->name('savings.goals.destroy');
    Route::post('/savings/records', [PlanningController::class, 'storeSavingsRecord'])->name('savings.records.store');
    Route::put('/savings/records/{record}', [PlanningController::class, 'updateSavingsRecord'])->name('savings.records.update');
    Route::delete('/savings/records/{record}', [PlanningController::class, 'destroySavingsRecord'])->name('savings.records.destroy');

    // 11. Settings & Payment Methods
    Route::get('/settings', [ReportAndSettingsController::class, 'settings'])->name('settings.index');
    Route::put('/settings', [ReportAndSettingsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/payment-methods', [ReportAndSettingsController::class, 'storePaymentMethod'])->name('settings.payment-methods.store');
    Route::delete('/settings/payment-methods/{paymentMethod}', [ReportAndSettingsController::class, 'destroyPaymentMethod'])->name('settings.payment-methods.destroy');

    // 12. User Profile, 2FA Security, Guided Tour & Workspace Reset
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/two-factor/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.two-factor.enable');
    Route::post('/profile/two-factor/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
    Route::post('/profile/two-factor/recovery-codes', [ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes');
    Route::post('/workspace/tour-complete', [ProfileController::class, 'completeTour'])->name('workspace.tour.complete');
    Route::post('/workspace/reset', [ProfileController::class, 'resetWorkspace'])->name('workspace.reset');

    // 13. SaaS Admin User Approvals & Provisioning
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{targetUser}/approve', [AdminUserController::class, 'approve'])->name('admin.users.approve');
    Route::post('/admin/users/{targetUser}/reject', [AdminUserController::class, 'reject'])->name('admin.users.reject');

    // 14. Header Notification Bell Actions (Read / Unread / Visit / Clear All)
    Route::get('/notifications/{notification}/visit', [AdminUserController::class, 'visitNotification'])->name('notifications.visit');
    Route::post('/notifications/{notification}/toggle-read', [AdminUserController::class, 'toggleNotificationRead'])->name('notifications.toggle-read');
    Route::post('/notifications/mark-all-read', [AdminUserController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [AdminUserController::class, 'clearAllNotifications'])->name('notifications.clear-all');
    Route::delete('/notifications/{notification}', [AdminUserController::class, 'destroyNotification'])->name('notifications.destroy');
});
