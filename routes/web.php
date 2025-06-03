<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\DaftarKerjaController;


Volt::route('/', 'auth.login')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::get('/workers/create', [WorkerController::class, 'create'])->name('workers.create');
Route::post('/workers', [WorkerController::class, 'store'])->name('workers.store');
Route::resource('WorkTypes', DaftarKerjaController::class)->except(['show', 'edit', 'create']);

Route::get('/WorkTypes/create', [DaftarKerjaController::class, 'create'])->name('WorkTypes.create');
Route::post('/workTypes', [DaftarKerjaController::class, 'store'])->name('WorkTypes.store');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
