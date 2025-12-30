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
    
    Route::get('/generation/{generation}/progress', [App\Http\Controllers\GenerationController::class, 'progress'])
        ->name('generation.progress');
    
    Route::post('/generation/{generation}/refine', [App\Http\Controllers\GenerationController::class, 'refine'])
        ->name('generation.refine');

    Route::post('/language', [App\Http\Controllers\LanguageController::class, 'update'])
        ->name('language.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
