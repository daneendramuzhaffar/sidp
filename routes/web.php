<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('login');
// })->name('home');

Volt::route('/', 'auth.login')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::get('/workers/create', [\App\Http\Controllers\WorkerController::class, 'create'])->name('workers.create');
Route::post('/workers', [\App\Http\Controllers\WorkerController::class, 'store'])->name('workers.store');

Route::get('/WorkTypes/create', [\App\Http\Controllers\DaftarKerjaController::class, 'create'])->name('WorkTypes.create');
Route::post('/workTypes', [\App\Http\Controllers\DaftarKerjaController::class, 'store'])->name('WorkTypes.store');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
