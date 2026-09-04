<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('login', [SessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('login', [SessionController::class, 'store'])->middleware('guest');
Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('trips/active', [TripController::class, 'activeTrips'])->name('trips.active');
    Route::get('trips/{tripId}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::get('trips/{tripId}', [TripController::class, 'show'])->name('trips.show');
    Route::post('trips', [TripController::class, 'store'])->name('trips.store');
    Route::put('trips/{tripId}', [TripController::class, 'update'])->name('trips.update');
    Route::post('trips/{tripId}/checkpoint', [TripController::class, 'logCheckpoint'])->name('trips.checkpoint');
    Route::post('trips/{tripId}/close', [TripController::class, 'closeManually'])->name('trips.close');
    Route::post('trips/{tripId}/status', [TripController::class, 'updateStatus'])->name('trips.status');

    Route::get('/manage', [FleetController::class, 'index'])->name('manage');
    Route::post('/drivers', [FleetController::class, 'storeDriver'])->name('drivers.store');
    Route::post('/vehicles', [FleetController::class, 'storeVehicle'])->name('vehicles.store');
    Route::post('/corridors', [FleetController::class, 'storeCorridor'])->name('corridors.store');
});
