<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');
    
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
    
    // LLM Models API
    Route::get('/api/llm/models', [App\Http\Controllers\LlmModelController::class, 'index'])
        ->name('llm.models');
    
    Route::get('/wizard', function () {
        return Inertia::render('Wizard/Index');
    })->name('wizard.index');

    // Generation routes
    Route::post('/generation/generate', [App\Http\Controllers\GenerationController::class, 'generate'])
        ->name('generation.generate');
    
    Route::get('/generation/{generation}', [App\Http\Controllers\GenerationController::class, 'show'])
        ->name('generation.show');
    
    Route::post('/generation/{generation}/next', [App\Http\Controllers\GenerationController::class, 'generateNext'])
        ->name('generation.next');
    
    Route::post('/generation/{generation}/background', [App\Http\Controllers\GenerationController::class, 'continueInBackground'])
        ->name('generation.background');
    
    Route::get('/generation/{generation}/progress', [App\Http\Controllers\GenerationController::class, 'progress'])
        ->name('generation.progress');
    
    Route::post('/generation/{generation}/refine', [App\Http\Controllers\GenerationController::class, 'refine'])
        ->name('generation.refine');
    
    // Templates list
    Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'index'])
        ->name('templates.index');

    // Admin Panel - Protected by admin middleware
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');
        
        // User Management
        Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class);
        Route::post('users/{user}/credits', [App\Http\Controllers\Admin\UserManagementController::class, 'adjustCredits'])
            ->name('users.credits');
        Route::post('users/{user}/toggle-premium', [App\Http\Controllers\Admin\UserManagementController::class, 'togglePremium'])
            ->name('users.toggle-premium');
        Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserManagementController::class, 'toggleStatus'])
            ->name('users.toggle-status');
        
        // LLM Models Management
        Route::resource('models', App\Http\Controllers\Admin\LlmModelController::class);
        Route::post('models/reorder', [App\Http\Controllers\Admin\LlmModelController::class, 'reorder'])
            ->name('models.reorder');
        
        // Settings
        Route::get('settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::post('settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])
            ->name('settings.update');
        Route::post('settings/{key}/reset', [App\Http\Controllers\Admin\SettingsController::class, 'reset'])
            ->name('settings.reset');
        
        // Generation History
        Route::get('generations', [App\Http\Controllers\Admin\GenerationHistoryController::class, 'index'])
            ->name('generations.index');
        Route::get('generations/{generation}', [App\Http\Controllers\Admin\GenerationHistoryController::class, 'show'])
            ->name('generations.show');
        Route::post('generations/{generation}/refund', [App\Http\Controllers\Admin\GenerationHistoryController::class, 'refund'])
            ->name('generations.refund');
        Route::post('generations/{generation}/retry', [App\Http\Controllers\Admin\GenerationHistoryController::class, 'retry'])
            ->name('generations.retry');
    });

    Route::post('/language', [App\Http\Controllers\LanguageController::class, 'update'])
        ->name('language.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
