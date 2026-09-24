<?php

use App\Http\Controllers\DocumentController;
use App\Livewire\Doctor;
use App\Livewire\HeadNurse;
use App\Livewire\Nurse;
use App\Livewire\Patient;
use App\Livewire\Shared;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'role'])->group(function () {
    // Every role
    Route::get('/dashboard', Shared\Dashboard::class)->name('dashboard');
    Route::get('/notifications', Shared\Notifications::class)->name('notifications');
    Route::get('/appointments', Shared\Appointments::class)->name('appointments');
    Route::get('/records', Shared\Records::class)->name('records');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
        ->middleware('signed')->name('documents.download');

    // Patient
    Route::middleware('role:patient')->prefix('patient')->name('patient.')->group(function () {
        Route::get('/profile', Patient\Profile::class)->name('profile');
        Route::get('/book', Patient\BookAppointment::class)->name('book');
        Route::get('/documents', Patient\Documents::class)->name('documents');
    });

    // Staff (nurse, doctor, head nurse)
    Route::get('/patients', Shared\Patients::class)
        ->middleware('role:nurse,doctor,head_nurse')->name('patients');

    // Nurse (head nurse may also supervise)
    Route::get('/visits', Nurse\Visits::class)
        ->middleware('role:nurse,head_nurse')->name('nurse.visits');

    // Doctor
    Route::middleware('role:doctor')->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/availability', Doctor\Availability::class)->name('availability');
        Route::get('/consultations', Doctor\Consultation::class)->name('consultations');
    });

    // Head nurse
    Route::middleware('role:head_nurse')->prefix('admin')->name('headnurse.')->group(function () {
        Route::get('/staff/{type}', HeadNurse\Staff::class)->name('staff'); // nurses | doctors
        Route::get('/audit-log', HeadNurse\AuditLog::class)->name('audit');
        Route::get('/reports', HeadNurse\Reports::class)->name('reports');
    });
});

// Breeze / Fortify auth routes (login, register, logout, password reset)
require __DIR__.'/auth.php';
