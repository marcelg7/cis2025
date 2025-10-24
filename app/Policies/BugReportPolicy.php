<?php

namespace App\Policies;

use App\Models\BugReport;
use App\Models\User;

class BugReportPolicy
{
    /**
     * Determine if the user can view any bug reports (admin only)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can view a bug report
     * Users can view their own reports, admins can view all
     */
    public function view(User $user, BugReport $bugReport): bool
    {
        return $user->hasRole('admin') || $user->id === $bugReport->user_id;
    }

    /**
     * Determine if the user can update a bug report (admin only)
     */
    public function update(User $user, BugReport $bugReport): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can delete a bug report (admin only)
     */
    public function delete(User $user, BugReport $bugReport): bool
    {
        return $user->hasRole('admin');
    }
}
