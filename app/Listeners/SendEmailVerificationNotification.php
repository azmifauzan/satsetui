<?php

namespace App\Listeners;

use App\Channels\TelegramChannel;
use App\Models\AdminSetting;
use App\Models\User;
use App\Notifications\UserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Send Email Verification Notification
 * 
 * Listens to Registered event and sends email verification
 * and notifies admin via Telegram
 */
class SendEmailVerificationNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Send email verification to user
        if ($event->user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $event->user->sendEmailVerificationNotification();
        }

        // Send Telegram notification to admin if enabled
        $telegramEnabled = AdminSetting::get('notification.telegram_enabled', false);
        
        if ($telegramEnabled) {
            $adminUsers = User::where('is_admin', true)->get();
            
            foreach ($adminUsers as $admin) {
                $admin->notify(new UserRegistered($event->user));
            }
        }
    }
}
