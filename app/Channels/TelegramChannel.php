<?php

namespace App\Channels;

use App\Services\TelegramService;
use Illuminate\Notifications\Notification;

/**
 * Telegram Notification Channel
 * 
 * Custom channel for sending notifications via Telegram
 */
class TelegramChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toTelegram($notifiable);

        $this->telegramService->sendMessage(
            $message['text'],
            $message['parse_mode'] ?? 'Markdown'
        );
    }
}
