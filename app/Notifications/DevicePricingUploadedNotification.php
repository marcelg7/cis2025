<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DevicePricingUploadedNotification extends Notification
{
    use Queueable;

    protected $deviceCount;
    protected $uploadedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $deviceCount, string $uploadedBy)
    {
        $this->deviceCount = $deviceCount;
        $this->uploadedBy = $uploadedBy;
    }

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
     * Get the array representation of the notification (for database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'device_pricing_uploaded',
            'title' => 'New Device Pricing Available',
            'message' => "{$this->deviceCount} device pricing records were uploaded by {$this->uploadedBy}",
            'device_count' => $this->deviceCount,
            'uploaded_by' => $this->uploadedBy,
            'action_url' => route('bell-pricing.index'),
            'action_text' => 'View Devices',
        ];
    }
}
