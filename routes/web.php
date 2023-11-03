<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ProjectReportController;

Route::middleware(['auth'])->group(function () {
    Route::resource('timer', TimerController::class)->only(['store', 'update', 'destroy']);

    Route::get('/dashboard', [TimerController::class, 'index'])
        ->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show', 'edit']);

    Route::resource('projects', ProjectController::class)->except(['show', 'edit']);

    Route::resource('websites', WebsiteController::class)->only(['store', 'update', 'destroy']);

    Route::resource('users', UserController::class)->except(['show']);

    Route::post('/users/upload-profile-image', [AvatarController::class, 'store']);

    Route::resource('teams', TeamController::class)->except(['show', 'edit']);

    Route::get('/reports', [ReportController::class, 'index']);

    Route::get('/project-reports', [ProjectReportController::class, 'index']);

    Route::post('/reports', [ReportController::class, 'index']);

    Route::get('/get-client-projects/{client}', [ClientController::class, 'getClientProjects']);

    Route::get('/get-client-websites/{client}', [ClientController::class, 'getClientWebsites']);

});

require __DIR__.'/auth.php';