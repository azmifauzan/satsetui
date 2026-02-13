<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Service
 * 
 * Handles sending notifications to Telegram
 */
class TelegramService
{
    /**
     * Send a message to Telegram
     */
    public function sendMessage(string $text, ?string $parseMode = 'Markdown'): bool
    {
        $botToken = AdminSetting::get('notification.telegram_bot_token');
        $chatId = AdminSetting::get('notification.telegram_chat_id');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram notification settings not configured');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode,
            ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent successfully');
                return true;
            }

            Log::error('Failed to send Telegram notification', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception sending Telegram notification', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Test the Telegram connection
     */
    public function testConnection(): array
    {
        $botToken = AdminSetting::get('notification.telegram_bot_token');

        if (!$botToken) {
            return [
                'success' => false,
                'message' => 'Bot token not configured',
            ];
        }

        try {
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getMe");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'bot' => $data['result'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to Telegram',
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }
}
