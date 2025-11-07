<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\User;
use App\Notifications\BugReportResolvedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class BugReportController extends Controller
{
    /**
     * Display bug report form (modal/page)
     */
    public function create()
    {
        return view('bug-reports.create');
    }

    /**
     * Store a new bug report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'category' => 'nullable|string',
            'url' => 'nullable|url',
            'screenshot' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Handle screenshot upload
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = $request->file('screenshot')->store('bug-reports', 'public');
        }

        // Create bug report
        $bugReport = BugReport::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'category' => $validated['category'] ?? 'other',
            'url' => $validated['url'] ?? $request->headers->get('referer'),
            'browser_info' => $request->userAgent(),
            'screenshot' => $screenshotPath,
        ]);

        // Send Slack notification
        $this->sendSlackNotification($bugReport);

        return redirect()->route('bug-reports.create')
            ->with('success', 'Bug report submitted successfully! We\'ll look into it.');
    }

    /**
     * Display all bug reports (admin only)
     */
    public function index()
    {
        $this->authorize('viewAny', BugReport::class);

        $bugReports = BugReport::with(['user', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('bug-reports.index', compact('bugReports'));
    }

    /**
     * Display a single bug report
     */
    public function show(BugReport $bugReport)
    {
        $this->authorize('view', $bugReport);

        $bugReport->load(['user', 'assignedTo']);

        return view('bug-reports.show', compact('bugReport'));
    }

    /**
     * Update bug report (admin only)
     */
    public function update(Request $request, BugReport $bugReport)
    {
        $this->authorize('update', $bugReport);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'severity' => 'required|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
            'admin_notes' => 'nullable|string',
        ]);

        // Check if status is changing to resolved (before update)
        $statusChangedToResolved = ($validated['status'] === 'resolved' && $bugReport->status !== 'resolved');

        // Set resolved_at timestamp if status changed to resolved
        if ($statusChangedToResolved) {
            $validated['resolved_at'] = now();
        }

        $bugReport->update($validated);

        // Notify the user who reported the bug when it's resolved
        if ($statusChangedToResolved) {
            $bugReport->user->notify(new BugReportResolvedNotification($bugReport));
        }

        return redirect()->route('bug-reports.show', $bugReport)
            ->with('success', 'Bug report updated successfully!');
    }

    /**
     * Delete bug report (admin only)
     */
    public function destroy(BugReport $bugReport)
    {
        $this->authorize('delete', $bugReport);

        // Delete screenshot if exists
        if ($bugReport->screenshot) {
            Storage::disk('public')->delete($bugReport->screenshot);
        }

        $bugReport->delete();

        return redirect()->route('bug-reports.index')
            ->with('success', 'Bug report deleted successfully!');
    }

    /**
     * Send Slack notification for new bug report
     */
    protected function sendSlackNotification(BugReport $bugReport)
    {
        $webhookUrl = env('BUG_REPORT_SLACK_WEBHOOK');
        $slackToken = env('SLACK_BOT_TOKEN'); // Optional: for getting thread link

        if (!$webhookUrl) {
            return;
        }

        $severityEmoji = [
            'low' => ':white_circle:',
            'medium' => ':large_yellow_circle:',
            'high' => ':large_orange_circle:',
            'critical' => ':red_circle:',
        ];

        $message = [
            'text' => '🐛 New Bug Report',
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🐛 New Bug Report',
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Title:*\n{$bugReport->title}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Severity:*\n{$severityEmoji[$bugReport->severity]} {$bugReport->severityInfo['label']}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Reported By:*\n{$bugReport->user->name}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Category:*\n" . (BugReport::CATEGORIES[$bugReport->category] ?? 'Other'),
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Description:*\n" . substr($bugReport->description, 0, 200) . (strlen($bugReport->description) > 200 ? '...' : ''),
                    ],
                ],
            ],
        ];

        // Add URL if available
        if ($bugReport->url) {
            $message['blocks'][] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*URL:*\n<{$bugReport->url}|View Page>",
                ],
            ];
        }

        // Add link to bug report
        $message['blocks'][] = [
            'type' => 'actions',
            'elements' => [
                [
                    'type' => 'button',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => 'View Bug Report',
                    ],
                    'url' => route('bug-reports.show', $bugReport->id),
                    'style' => 'primary',
                ],
            ],
        ];

        // If we have a bot token, use the Web API to get thread info
        if ($slackToken) {
            $channel = env('SLACK_CHANNEL_ID');
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$slackToken}",
                'Content-Type' => 'application/json',
            ])->post('https://slack.com/api/chat.postMessage', [
                'channel' => $channel,
                'text' => $message['text'],
                'blocks' => $message['blocks'],
            ]);

            if ($response->successful() && $response->json('ok')) {
                $data = $response->json();
                $bugReport->update([
                    'slack_thread_ts' => $data['ts'] ?? null,
                    'slack_channel_id' => $data['channel'] ?? null,
                ]);
            }
        } else {
            // Fallback to webhook (no thread info captured)
            Http::post($webhookUrl, $message);
        }
    }
}
