<?php

namespace App\Console\Commands;

use App\Models\BugReport;
use Illuminate\Console\Command;

class ExportBugReport extends Command
{
    protected $signature = 'bug:export {id? : The bug report ID to export}
                            {--list : List all bugs}
                            {--open : Filter for open bugs only}
                            {--assigned= : Filter by assigned user ID}
                            {--severity= : Filter by severity (low, medium, high, critical)}
                            {--limit=20 : Number of bugs to show in list}';

    protected $description = 'Export bug report details in a format suitable for sharing with Claude Code';

    public function handle()
    {
        $bugId = $this->argument('id');
        $list = $this->option('list');

        if ($list || !$bugId) {
            return $this->listBugs();
        }

        return $this->exportBug($bugId);
    }

    protected function exportBug($id)
    {
        $bug = BugReport::with(['user', 'assignedTo'])->find($id);

        if (!$bug) {
            $this->error("Bug report #{$id} not found.");
            return 1;
        }

        // Output formatted bug details
        $this->line('');
        $this->line('================================================================================');
        $this->info("BUG REPORT #{$bug->id}");
        $this->line('================================================================================');
        $this->line('');

        $this->line("Title: {$bug->title}");
        $this->line("Status: {$bug->status}");
        $this->line("Severity: {$bug->severity}");
        $this->line("Category: " . ($bug->category ?? 'N/A'));
        $this->line('');

        $this->line("Reported By: {$bug->user->name} ({$bug->user->email})");
        $this->line("Reported At: {$bug->created_at->format('Y-m-d H:i:s')}");

        if ($bug->assignedTo) {
            $this->line("Assigned To: {$bug->assignedTo->name} ({$bug->assignedTo->email})");
        } else {
            $this->line("Assigned To: Unassigned");
        }

        if ($bug->resolved_at) {
            $this->line("Resolved At: {$bug->resolved_at->format('Y-m-d H:i:s')}");
        }

        $this->line('');
        $this->line('--- Description ---');
        $this->line($bug->description);
        $this->line('');

        if ($bug->url) {
            $this->line("Page URL: {$bug->url}");
        }

        if ($bug->browser_info) {
            $this->line("Browser Info: {$bug->browser_info}");
        }

        if ($bug->screenshot) {
            $this->line("Screenshot: storage/app/public/{$bug->screenshot}");
        }

        if ($bug->admin_notes) {
            $this->line('');
            $this->line('--- Admin Notes ---');
            $this->line($bug->admin_notes);
        }

        $this->line('');
        $this->line('================================================================================');
        $this->line('');

        $this->comment('To fix this bug, copy the above details and share with Claude Code.');
        $this->comment('After fixing, update the bug status via the web interface or database.');

        return 0;
    }

    protected function listBugs()
    {
        $query = BugReport::with(['user', 'assignedTo']);

        // Apply filters
        if ($this->option('open')) {
            $query->whereIn('status', ['open', 'in_progress']);
        }

        if ($this->option('assigned')) {
            $query->where('assigned_to', $this->option('assigned'));
        }

        if ($this->option('severity')) {
            $query->where('severity', $this->option('severity'));
        }

        $limit = (int) $this->option('limit');
        $bugs = $query->orderBy('created_at', 'desc')
                      ->limit($limit)
                      ->get();

        if ($bugs->isEmpty()) {
            $this->info('No bug reports found matching the criteria.');
            return 0;
        }

        $this->line('');
        $this->line('================================================================================');
        $this->info('BUG REPORTS');
        $this->line('================================================================================');
        $this->line('');

        $tableData = [];
        foreach ($bugs as $bug) {
            $tableData[] = [
                $bug->id,
                $bug->title,
                $bug->severity,
                $bug->status,
                $bug->category ?? 'N/A',
                $bug->assignedTo ? $bug->assignedTo->name : 'Unassigned',
                $bug->created_at->format('Y-m-d'),
            ];
        }

        $this->table(
            ['ID', 'Title', 'Severity', 'Status', 'Category', 'Assigned To', 'Date'],
            $tableData
        );

        $this->line('');
        $this->comment("Showing {$bugs->count()} bug report(s).");
        $this->comment('To view details: php artisan bug:export {id}');
        $this->line('');

        $this->info('Available options:');
        $this->line('  --open              Show only open/in-progress bugs');
        $this->line('  --assigned=USER_ID  Show bugs assigned to specific user');
        $this->line('  --severity=LEVEL    Filter by severity (low, medium, high, critical)');
        $this->line('  --limit=N           Limit number of results (default: 20)');

        return 0;
    }
}
