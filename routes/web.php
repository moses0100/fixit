<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RepairRequestController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/repairs/suggest', [RepairRequestController::class, 'suggest'])->name('repairs.suggest');
    Route::get('/repairs/{repair}/image', [RepairRequestController::class, 'image'])->name('repairs.image');
    Route::get('/repairs/{repair}/slip', [RepairRequestController::class, 'slip'])->name('repairs.slip');
    Route::get('/repairs/{repair}/slip-pdf', [RepairRequestController::class, 'slipPdf'])->name('repairs.slip-pdf');
    Route::patch('/repairs/{repair}/cancel', [RepairRequestController::class, 'cancel'])->name('repairs.cancel');
    Route::resource('repairs', RepairRequestController::class)->except('destroy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::redirect('/dashboard', '/admin');
        Route::get('/repairs/export', [AdminController::class, 'export'])->name('repairs.export');
        Route::get('/repairs', [AdminController::class, 'index'])->name('repairs.index');
        Route::get('/repairs/{repair}', [AdminController::class, 'show'])->name('repairs.show');
        Route::put('/repairs/{repair}/status', [AdminController::class, 'updateStatus'])->name('repairs.status');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/password', [AdminUserController::class, 'updatePassword'])->name('users.password');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::post('/users/{user}/reset-link', [AdminUserController::class, 'sendResetLink'])->name('users.reset-link');
    });
});

require __DIR__.'/auth.php';
