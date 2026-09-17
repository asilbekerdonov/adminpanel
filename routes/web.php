<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\Structure\BranchController;
use App\Http\Controllers\Structure\DepartmentController;
use App\Http\Controllers\Structure\PositionController;
use App\Http\Controllers\Structure\SubdivisionController;
use App\Http\Controllers\VacancyRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Уведомления (bell icon) — доступны всем авторизованным ──
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    // ── Заявки на подбор персонала ─────────────────────────────
    // Видимость и действия ограничены VacancyRequestPolicy.
    Route::resource('statements', VacancyRequestController::class);

    Route::prefix('statements/{statement}')->name('statements.')->group(function () {
        Route::post('submit-to-hr', [VacancyRequestController::class, 'submit'])->name('submit-to-hr');
        Route::post('send-to-supervisor', [VacancyRequestController::class, 'sendToSupervisor'])->name('send-to-supervisor');
        Route::post('approve', [VacancyRequestController::class, 'approve'])->name('approve');
        Route::post('reject', [VacancyRequestController::class, 'reject'])->name('reject');
        Route::post('hold', [VacancyRequestController::class, 'onHold'])->name('hold');
        Route::post('confirm-close', [VacancyRequestController::class, 'confirmClose'])->name('confirm-close');
    });

    // ── Резюме из внешней БД resume_bot ────────────────────────
    Route::delete('resumes/{resume}', [ResumeController::class, 'destroy'])->name('resumes.destroy');

    // ── Оргструктура: управление ───────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('branches', BranchController::class)->only(['index', 'store', 'destroy']);
        Route::resource('departments', DepartmentController::class)->only(['store', 'destroy']);
        Route::resource('subdivisions', SubdivisionController::class)->only(['store', 'destroy']);
        Route::resource('positions', PositionController::class)->only(['store', 'destroy']);
    });

    // ── Оргструктура: просмотр (руководитель и HR) ─────────────
    Route::middleware('role:super_admin|hr_manager')->group(function () {
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('subdivisions', SubdivisionController::class)->only(['index']);
        Route::resource('positions', PositionController::class)->only(['index']);
    });

    // ── Сотрудник / руководитель подразделения ─────────────────
    Route::middleware('role:employee|department_head')->group(function () {
        Route::get('employee/subdivision', [SubdivisionController::class, 'mine'])
            ->name('employee.subdivision.index');
    });
});

require __DIR__.'/auth.php';
