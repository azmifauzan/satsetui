<?php

namespace App\Notifications;

use App\Models\Generation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemplateGenerationCompleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Generation $generation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'generation_id' => $this->generation->id,
            'template_name' => $this->generation->project->name,
            'status' => $this->generation->status,
            'total_pages' => $this->generation->total_pages,
            'message' => "Template '{$this->generation->project->name}' generation completed!",
        ];
    }
}
