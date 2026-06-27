<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Adviser\AdviserController;
use App\Http\Controllers\Principal\PrincipalController;

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'principal') {
            return redirect()->route('principal.dashboard');
        }
        return redirect()->route('adviser.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (auth()->user()->role === 'principal') {
        return redirect()->route('principal.dashboard');
    }
    return redirect()->route('adviser.dashboard');
})->middleware('auth')->name('dashboard');

// =============================================
// ADVISER ROUTES
// =============================================
Route::middleware(['auth', 'role:adviser'])
    ->prefix('adviser')
    ->name('adviser.')
    ->group(function () {
        Route::get('/dashboard', [AdviserController::class, 'dashboard'])->name('dashboard');
        Route::get('/students', [AdviserController::class, 'students'])->name('students');
        Route::get('/students/{id}/edit', [AdviserController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{id}', [AdviserController::class, 'updateStudent'])->name('students.update');
        Route::get('/grades', [AdviserController::class, 'grades'])->name('grades');
        Route::post('/grades', [AdviserController::class, 'storeGrade'])->name('grades.store');

        // Submit Report
        Route::get('/submit-report', [AdviserController::class, 'showSubmitReport'])->name('submit.report');
        Route::post('/submit-report', [AdviserController::class, 'submitReport'])->name('submit.report.post');
    });

// =============================================
// PRINCIPAL / ADMIN ROUTES
// =============================================
Route::middleware(['auth', 'role:principal'])
    ->prefix('principal')
    ->name('principal.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [PrincipalController::class, 'dashboard'])->name('dashboard');

        // User Management
        Route::get('/users', [PrincipalController::class, 'users'])->name('users');
        Route::post('/users', [PrincipalController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [PrincipalController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [PrincipalController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [PrincipalController::class, 'destroyUser'])->name('users.destroy');

        // Sections Management
        Route::get('/sections', [PrincipalController::class, 'sections'])->name('sections');
        Route::post('/sections', [PrincipalController::class, 'storeSection'])->name('sections.store');
        Route::put('/sections/{id}', [PrincipalController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{id}', [PrincipalController::class, 'destroySection'])->name('sections.destroy');

        // ✅ BAGO — Tracks Management
        Route::get('/tracks', [PrincipalController::class, 'tracks'])->name('tracks');
        Route::post('/tracks', [PrincipalController::class, 'storeTrack'])->name('tracks.store');
        Route::put('/tracks/{id}', [PrincipalController::class, 'updateTrack'])->name('tracks.update');
        Route::delete('/tracks/{id}', [PrincipalController::class, 'destroyTrack'])->name('tracks.destroy');

        // ✅ BAGO — Specializations Management
        Route::get('/specializations', [PrincipalController::class, 'specializations'])->name('specializations');
        Route::post('/specializations', [PrincipalController::class, 'storeSpecialization'])->name('specializations.store');
        Route::put('/specializations/{id}', [PrincipalController::class, 'updateSpecialization'])->name('specializations.update');
        Route::delete('/specializations/{id}', [PrincipalController::class, 'destroySpecialization'])->name('specializations.destroy');

        // ✅ BAGO — Subjects Management
        Route::get('/subjects', [PrincipalController::class, 'subjects'])->name('subjects');
        Route::post('/subjects', [PrincipalController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{id}', [PrincipalController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{id}', [PrincipalController::class, 'destroySubject'])->name('subjects.destroy');

        // Students Management
        Route::get('/students', [PrincipalController::class, 'students'])->name('students');
        Route::post('/students', [PrincipalController::class, 'storeStudent'])->name('students.store');
        Route::put('/students/{id}', [PrincipalController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{id}', [PrincipalController::class, 'destroyStudent'])->name('students.destroy');

        // Reports
        Route::get('/reports', [PrincipalController::class, 'reports'])->name('reports');

        // ✅ BAGO — AJAX route para sa specializations dropdown
        Route::get('/specializations-by-track/{trackId}', [PrincipalController::class, 'getSpecializationsByTrack'])->name('specializations.by.track');
        // Activity Logs
        Route::get('/activity-logs', [PrincipalController::class, 'activityLogs'])->name('activity.logs');
    });

require __DIR__.'/auth.php';