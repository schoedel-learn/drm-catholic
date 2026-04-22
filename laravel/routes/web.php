<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page - Vue/Inertia (keep unchanged)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Public form routes (no auth required)
Route::get('/forms/{token}', [\App\Http\Controllers\PublicFormController::class, 'show'])->name('public-form.show');
Route::post('/forms/{token}', [\App\Http\Controllers\PublicFormController::class, 'submit'])->name('public-form.submit');

// Authenticated Velzon routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    // Dashboard - Velzon Blade
    Route::get('/dashboard', [\App\Http\Controllers\VelzonController::class, 'dashboard'])->name('dashboard');

    // Analytics - Velzon Blade
    Route::get('/analytics', [\App\Http\Controllers\VelzonController::class, 'analytics'])->name('analytics');

    // Admin routes (Velzon Blade)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/contacts', [\App\Http\Controllers\VelzonController::class, 'show'])->name('contacts');
        Route::get('/organizations', [\App\Http\Controllers\VelzonController::class, 'show'])->name('organizations');
        Route::get('/review-queue', [\App\Http\Controllers\VelzonController::class, 'show'])->name('review-queue');
        Route::get('/notifications', [\App\Http\Controllers\VelzonController::class, 'show'])->name('notifications');
    });

    // API routes for data (used by Velzon pages)
    Route::resource('contacts', \App\Http\Controllers\ContactController::class);
    Route::resource('organizations', \App\Http\Controllers\OrganizationController::class);
    Route::resource('custom-fields', \App\Http\Controllers\CustomFieldController::class)->only(['index', 'store', 'destroy']);

    // Form builder routes
    Route::resource('form-builder', \App\Http\Controllers\FormController::class)->except(['edit', 'update']);
    Route::post('/form-builder/{form}/toggle', [\App\Http\Controllers\FormController::class, 'toggleActive'])->name('form-builder.toggle');

    // Entity types
    Route::resource('entity-types', \App\Http\Controllers\EntityTypeController::class)->except(['create', 'show', 'edit']);

    // Clergy
    Route::resource('clergy', \App\Http\Controllers\ClergyController::class);

    // Calendar & Liturgical
    Route::get('/calendar', [\App\Http\Controllers\LiturgicalCalendarController::class, 'index'])->name('calendar');
    Route::get('/api/liturgical-calendar/events', [\App\Http\Controllers\LiturgicalCalendarController::class, 'events'])->name('liturgical.events');
    Route::get('/api/liturgical-calendar/today', [\App\Http\Controllers\LiturgicalCalendarController::class, 'today'])->name('liturgical.today');

    // Diocesan/Parish Calendar Events (CRUD)
    Route::apiResource('api/calendar/events', \App\Http\Controllers\CalendarEventController::class)
        ->parameters(['events' => 'event']);
});

