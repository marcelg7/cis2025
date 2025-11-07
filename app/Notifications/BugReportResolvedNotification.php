<?php

namespace App\Notifications;

use App\Models\BugReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugReportResolvedNotification extends Notification
{
    use Queueable;

    protected $bugReport;

    /**
     * Create a new notification instance.
     */
    public function __construct(BugReport $bugReport)
    {
        $this->bugReport = $bugReport;
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
            'type' => 'bug_report_resolved',
            'title' => 'Bug Report Resolved',
            'message' => "Your bug report '{$this->bugReport->title}' has been resolved.",
            'bug_report_id' => $this->bugReport->id,
            'bug_report_title' => $this->bugReport->title,
            'action_url' => route('bug-reports.show', $this->bugReport->id),
            'action_text' => 'View Bug Report',
        ];
    }
}
