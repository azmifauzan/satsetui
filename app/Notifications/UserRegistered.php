<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * User Registered Notification (for Admin via Telegram)
 * 
 * Notifies admin when a new user registers
 */
class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the array representation of the notification for Telegram.
     *
     * @return array<string, mixed>
     */
    public function toTelegram(object $notifiable): array
    {
        return [
            'text' => "🎉 *Pengguna Baru Terdaftar*\n\n" .
                      "👤 Nama: {$this->user->name}\n" .
                      "📧 Email: {$this->user->email}\n" .
                      "🆔 ID: {$this->user->id}\n" .
                      "📅 Tanggal: " . $this->user->created_at->format('d/m/Y H:i') . "\n" .
                      "💰 Credits: {$this->user->credits}",
            'parse_mode' => 'Markdown',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ];
    }
}
