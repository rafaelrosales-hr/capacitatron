<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\CatalogosController;
use App\Http\Controllers\PdfController;
use Livewire\Volt\Volt;
use App\Http\Controllers\CertificateController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);
});
Route::middleware(['auth', 'media'])->group(function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Volt::route('my-courses', 'pages.my_courses')
        ->name('my_courses');

    Volt::route('course-requests', 'pages.course-requests')
        ->name('course-requests');

    Volt::route('reply-course', 'pages.reply_course')
        ->name('reply-course');

    Volt::route('calendario-cursos', 'pages.calendar-cursos')
        ->name('calendar.cursos');

    Volt::route('profile.edit', 'pages.profile')
        ->name('profile.edit');
    Volt::route('pase-lista', 'pages.pase_lista')
        ->name('pase_lista')->middleware(['permission:pase_lista']);

    Volt::route('reporte-usuarios-cursos', 'pages.reporte-usuarios-cursos')
        ->name('reporte.usuarios.cursos')->middleware(['permission:reporte']);

    Volt::route('cargaDeCursos', 'pages.cursos-hr')
        ->name('cursos.hr')->middleware(['permission:cursos_media']);

    Volt::route('courses-manager', 'pages.courses-manager')
        ->name('courses-manager')->middleware(['permission:cursos_media']);

    Volt::route('courses/{course}/edit', 'pages.courses-edit')
        ->name('courses.edit')->middleware(['permission:cursos_media']);

    Volt::route('attendace-report', 'pages.attendance-report')
        ->name('attendace.report')->middleware(['permission:attendace_report']);

    Route::get('/pdf/user-courses/{userId}', [PdfController::class, 'userCourses'])
        ->name('pdf.user.courses');

    Route::get('/verificar-certificado/{folio}', [CertificateController::class, 'verify'])
        ->name('certificate.verify');

    Route::get('/catalogos', [CatalogosController::class, 'index'])
        ->name('catalogos')->middleware(['permission:catalogos']);

    Volt::route('carga-usuarios-media', 'pages.carga-users')
        ->name('carga-users')->middleware(['permission:carga_usuarios']);

    Volt::route('/users-manager', 'pages.table-users')
        ->name('users-manager')->middleware(['permission:carga_usuarios']);

    Volt::route('/users/{user}/edit', 'pages.edit-user')
        ->name('users.edit')->middleware(['permission:carga_usuarios']);
});

require __DIR__ . '/auth.php';

//->middleware(['permission:ver_reportes']);