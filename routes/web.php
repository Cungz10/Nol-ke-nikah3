<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\VendorController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        // Timeline & Roadmap
        Route::get('timeline', [TimelineController::class, 'index'])->name('timeline.index');
        Route::post('timeline/tasks', [TimelineController::class, 'storeTask'])->name('timeline.tasks.store');
        Route::put('timeline/tasks/{task}', [TimelineController::class, 'updateTask'])->name('timeline.tasks.update');
        Route::delete('timeline/tasks/{task}', [TimelineController::class, 'destroyTask'])->name('timeline.tasks.destroy');

        // Vendors
        Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');

        // Budget
        Route::get('budget', [BudgetController::class, 'index'])->name('budget.index');
        Route::post('budget/allocations', [BudgetController::class, 'updateAllocations'])->name('budget.allocations.update');
        Route::post('budget/payments', [BudgetController::class, 'storePayment'])->name('budget.payments.store');
        Route::delete('budget/payments/{payment}', [BudgetController::class, 'destroyPayment'])->name('budget.payments.destroy');

        // Documents (Private Storage)
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
