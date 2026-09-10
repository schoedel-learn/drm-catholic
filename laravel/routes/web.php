<?php

use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClergyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\EntityTypeController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LiturgicalCalendarController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\VelzonController;
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
Route::get('/forms/{token}', [PublicFormController::class, 'show'])->name('public-form.show');
Route::post('/forms/{token}', [PublicFormController::class, 'submit'])->name('public-form.submit');

// Authenticated application routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Legacy Velzon routes kept for compatibility while core preview uses Inertia pages.
    Route::get('/analytics', [VelzonController::class, 'analytics'])->name('analytics');

    // Legacy admin URLs redirect to the live Inertia screens.
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/contacts', '/contacts')->name('contacts');
        Route::redirect('/organizations', '/organizations')->name('organizations');
        Route::get('/review-queue', [VelzonController::class, 'show'])->name('review-queue');
        Route::get('/notifications', [VelzonController::class, 'show'])->name('notifications');
    });

    Route::resource('contacts', ContactController::class);
    Route::resource('organizations', OrganizationController::class);
    Route::resource('custom-fields', CustomFieldController::class)->only(['index', 'store', 'destroy']);

    // Form builder routes
    Route::resource('form-builder', FormController::class)->except(['edit', 'update']);
    Route::post('/form-builder/{form}/toggle', [FormController::class, 'toggleActive'])->name('form-builder.toggle');

    // Entity types
    Route::resource('entity-types', EntityTypeController::class)->except(['create', 'show', 'edit']);

    // Clergy
    Route::resource('clergy', ClergyController::class);

    // Calendar & Liturgical
    Route::get('/calendar', [LiturgicalCalendarController::class, 'index'])->name('calendar');
    Route::get('/api/liturgical-calendar/events', [LiturgicalCalendarController::class, 'events'])->name('liturgical.events');
    Route::get('/api/liturgical-calendar/today', [LiturgicalCalendarController::class, 'today'])->name('liturgical.today');

    // Diocesan/Parish Calendar Events (CRUD)
    Route::apiResource('api/calendar/events', CalendarEventController::class)
        ->parameters(['events' => 'event']);
});
