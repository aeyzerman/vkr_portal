<?php

use App\Http\Controllers\Admin\StudyGroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты (Laravel Breeze/Fortify генерирует auth роуты сам)
Route::get('/', fn() => redirect()->route('dashboard'));

// Всё за auth middleware
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Темы ---
    Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
    Route::get('/topics/create', [TopicController::class, 'create'])->name('topics.create');
    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
    Route::get('/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
    Route::patch('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');

    // Заявка студента на тему
    Route::post('/topics/{topic}/apply', [TopicController::class, 'apply'])->name('topics.apply');
    // Руководитель назначает тему студенту
    Route::post('/topics/{topic}/assign', [TopicController::class, 'assign'])->name('topics.assign');
    // Одобрение темы (админ)
    Route::post('/topics/{topic}/approve', [TopicController::class, 'approve'])->name('topics.approve');

    // --- Работы ---
    Route::get('/thesis/my', [ThesisController::class, 'my'])->name('thesis.my');
    Route::get('/thesis', [ThesisController::class, 'index'])->name('thesis.index');
    Route::get('/thesis/{thesis}', [ThesisController::class, 'show'])->name('thesis.show');

    Route::post('/thesis/{thesis}/document', [ThesisController::class, 'uploadDocument'])->name('thesis.document.upload');
    Route::get('/thesis/{thesis}/document', [ThesisController::class, 'downloadDocument'])->name('thesis.document.download');
    Route::patch('/thesis/{thesis}/status', [ThesisController::class, 'updateStatus'])->name('thesis.status.update');

    // --- Админ панель ---
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {

        // Пользователи
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/assign-group', [UserController::class, 'assignGroup'])->name('users.assign-group');
        Route::post('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions');

        // Группы
        Route::get('/groups', [StudyGroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [StudyGroupController::class, 'create'])->name('groups.create');
        Route::post('/groups', [StudyGroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{studyGroup}', [StudyGroupController::class, 'show'])->name('groups.show');
        Route::get('/groups/{studyGroup}/edit', [StudyGroupController::class, 'edit'])->name('groups.edit');
        Route::patch('/groups/{studyGroup}', [StudyGroupController::class, 'update'])->name('groups.update');
    });
});

include __DIR__.'/auth.php';
