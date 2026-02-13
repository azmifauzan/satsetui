<?php

namespace App\Providers;

use App\Channels\TelegramChannel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Telegram notification channel
        Notification::extend('telegram', function ($app) {
            return $app->make(TelegramChannel::class);
        });

        // Register event listeners
        Event::listen(
            Registered::class,
            \App\Listeners\SendEmailVerificationNotification::class,
        );
    }
}
