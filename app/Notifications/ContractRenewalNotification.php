<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractRenewalNotification extends Notification
{
    use Queueable;

    protected $contract;
    protected $daysUntilExpiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contract $contract, int $daysUntilExpiry)
    {
        $this->contract = $contract;
        $this->daysUntilExpiry = $daysUntilExpiry;
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
            'type' => 'contract_renewal',
            'title' => 'Contract Renewal Opportunity',
            'message' => "Contract #{$this->contract->id} expires in {$this->daysUntilExpiry} days - potential renewal opportunity",
            'contract_id' => $this->contract->id,
            'days_until_expiry' => $this->daysUntilExpiry,
            'end_date' => $this->contract->end_date->format('M d, Y'),
            'action_url' => route('contracts.show', $this->contract->id),
            'action_text' => 'View Contract',
        ];
    }
}
