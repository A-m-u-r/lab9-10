<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasterClassController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/category/{category}', [CategoryController::class, 'show'])
    ->name('categories.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/cabinet', [CabinetController::class, 'index'])->name('cabinet');

    Route::get('/master-classes/create', [MasterClassController::class, 'create'])->name('master-classes.create');
    Route::post('/master-classes', [MasterClassController::class, 'store'])->name('master-classes.store');
    Route::get('/master-classes/{masterClass}', [MasterClassController::class, 'show'])->name('master-classes.show');
    Route::get('/master-classes/{masterClass}/edit', [MasterClassController::class, 'edit'])->name('master-classes.edit');
    Route::put('/master-classes/{masterClass}', [MasterClassController::class, 'update'])->name('master-classes.update');

    Route::get('/booking/confirm/{masterClass}', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/booking/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/api/slots', [BookingController::class, 'slots'])->name('api.slots');
});
