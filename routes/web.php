<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Wizard is accessible without auth - login required only at generation
Route::get('/wizard', function () {
    $models = [];
    $userCredits = 0;

    if (Auth::check()) {
        $models = App\Models\LlmModel::active()->ordered()->get()->map(function ($model) {
            return [
                'id' => $model->model_type,
                'name' => $model->display_name,
                'description' => $model->description,
                'credits_required' => $model->base_credits,
            ];
        });
        $userCredits = Auth::user()->credits;
    }

    return Inertia::render('Wizard/Index', [
        'models' => $models,
        'userCredits' => $userCredits,
    ]);
})->name('wizard.index');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    // Email Verification
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    // Email Verification Notice
    Route::get('/verify-email', function () {
        return Inertia::render('Auth/VerifyEmail');
    })->middleware('throttle:6,1')->name('verification.notice');

    // Resend Email Verification
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Routes that require verified email
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
            ->name('dashboard');

        // LLM Models API
        Route::get('/api/llm/models', [App\Http\Controllers\LlmModelController::class, 'index'])
            ->name('llm.models');

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

        Route::get('/generation/{generation}/stream', [App\Http\Controllers\GenerationController::class, 'stream'])
            ->name('generation.stream');

        Route::post('/generation/{generation}/refine', [App\Http\Controllers\GenerationController::class, 'refine'])
            ->name('generation.refine');

        Route::patch('/generation/{generation}/name', [App\Http\Controllers\GenerationController::class, 'updateName'])
            ->name('generation.updateName');

        // Preview routes
        Route::post('/generation/{generation}/preview/setup', [App\Http\Controllers\PreviewController::class, 'setup'])
            ->name('preview.setup');

        Route::get('/generation/{generation}/preview/status', [App\Http\Controllers\PreviewController::class, 'status'])
            ->name('preview.status');

        Route::get('/generation/{generation}/preview/proxy/{path?}', [App\Http\Controllers\PreviewController::class, 'proxy'])
            ->where('path', '.*')
            ->name('preview.proxy');

        Route::post('/generation/{generation}/preview/stop', [App\Http\Controllers\PreviewController::class, 'stop'])
            ->name('preview.stop');

        Route::get('/generation/{generation}/preview/static/{path?}', [App\Http\Controllers\PreviewController::class, 'serveStatic'])
            ->where('path', '.*')
            ->name('preview.static');

        Route::get('/generation/{generation}/files', [App\Http\Controllers\PreviewController::class, 'fileTree'])
            ->name('generation.files');

        Route::get('/generation/{generation}/files/{fileId}', [App\Http\Controllers\PreviewController::class, 'fileContent'])
            ->name('generation.fileContent');

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

            // LLM Models Management (2 Fixed Models: Satset & Expert)
            Route::get('models', [App\Http\Controllers\Admin\LlmModelController::class, 'index'])
                ->name('models.index');
            Route::get('models/{model}/edit', [App\Http\Controllers\Admin\LlmModelController::class, 'edit'])
                ->name('models.edit');
            Route::put('models/{model}', [App\Http\Controllers\Admin\LlmModelController::class, 'update'])
                ->name('models.update');
            Route::post('models/{model}/toggle-active', [App\Http\Controllers\Admin\LlmModelController::class, 'toggleActive'])
                ->name('models.toggle-active');

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
    }); // end of verified middleware

    Route::post('/language', [App\Http\Controllers\LanguageController::class, 'update'])
        ->name('language.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
