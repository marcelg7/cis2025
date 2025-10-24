<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractPendingSignatureNotification extends Notification
{
    use Queueable;

    protected $contract;
    protected $hoursPending;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contract $contract, int $hoursPending)
    {
        $this->contract = $contract;
        $this->hoursPending = $hoursPending;
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
            'type' => 'contract_pending_signature',
            'title' => 'Contract Pending Signature',
            'message' => "Contract #{$this->contract->id} has been pending signature for {$this->hoursPending} hours",
            'contract_id' => $this->contract->id,
            'hours_pending' => $this->hoursPending,
            'action_url' => route('contracts.view', $this->contract->id),
            'action_text' => 'View Contract',
        ];
    }
}
