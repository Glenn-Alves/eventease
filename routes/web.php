<?php

use App\Http\Controllers\Organizer\DashboardController;
use App\Http\Controllers\Organizer\EventController;
use App\Http\Controllers\Organizer\FormDesignerController;
use App\Http\Controllers\Participant\EventController as ParticipantEventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return auth()->user()->role === 'organizer'
        ? redirect()->route('organizer.dashboard')
        : redirect()->route('participant.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:organizer'])
    ->prefix('organizer')->name('organizer.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('events', EventController::class);
        Route::get('events/{event}/form', [FormDesignerController::class, 'edit'])->name('events.form.edit');
        Route::put('events/{event}/form', [FormDesignerController::class, 'update'])->name('events.form.update');
        Route::get('events/{event}/preview', [FormDesignerController::class, 'preview'])->name('events.preview');
        Route::put('events/{event}/preview', [FormDesignerController::class, 'previewDraft'])->name('events.preview.draft');
    });

Route::middleware(['auth', 'role:participant'])
    ->prefix('participant')->name('participant.')->group(function () {
        Route::get('/dashboard', [ParticipantEventController::class, 'index'])->name('dashboard');
        Route::get('/events', fn () => redirect()->route('participant.dashboard', request()->query()))->name('events.index');
        Route::get('/events/{event}', [ParticipantEventController::class, 'show'])->name('events.show');
        Route::post('/events/{event}/register', [ParticipantEventController::class, 'register'])->name('events.register');
        Route::get('/registrations', [ParticipantEventController::class, 'myRegistrations'])->name('registrations.index');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';