<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FtpUploadFailedNotification extends Notification
{
    use Queueable;

    protected $contract;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contract $contract, string $errorMessage)
    {
        $this->contract = $contract;
        $this->errorMessage = $errorMessage;
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
            'type' => 'ftp_upload_failed',
            'title' => 'FTP Upload Failed',
            'message' => "Contract #{$this->contract->id} failed to upload to Vault: {$this->errorMessage}",
            'contract_id' => $this->contract->id,
            'error_message' => $this->errorMessage,
            'action_url' => route('contracts.view', $this->contract->id),
            'action_text' => 'View Contract',
        ];
    }
}
