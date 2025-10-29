<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    /**
     * Determine if the user can view any contracts.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view contracts list
        return true;
    }

    /**
     * Determine if the user can view the contract.
     */
    public function view(User $user, Contract $contract): bool
    {
        // All authenticated users can view any contract (team collaboration)
        return true;
    }

    /**
     * Determine if the user can create contracts.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create contracts
        return true;
    }

    /**
     * Determine if the user can update the contract.
     */
    public function update(User $user, Contract $contract): bool
    {
        // All authenticated users can update any draft contract (team collaboration)
        // But finalized contracts cannot be updated - use revisions instead
        return $contract->status !== 'finalized';
    }

    /**
     * Determine if the user can delete the contract.
     */
    public function delete(User $user, Contract $contract): bool
    {
        // Only admins can delete finalized contracts
        if ($user->hasRole('admin')) {
            return true;
        }

        // All users can delete draft contracts (team collaboration)
        return $contract->status === 'draft';
    }

    /**
     * Determine if the user can sign the contract.
     */
    public function sign(User $user, Contract $contract): bool
    {
        // All authenticated users can sign any contract (team collaboration)
        return true;
    }

    /**
     * Determine if the user can download the contract.
     */
    public function download(User $user, Contract $contract): bool
    {
        // All authenticated users can download any contract (team collaboration)
        return true;
    }

    /**
     * Determine if the user can finalize the contract.
     */
    public function finalize(User $user, Contract $contract): bool
    {
        // All authenticated users can finalize any contract (team collaboration)
        return true;
    }

    /**
     * Determine if the user can create a revision of the contract.
     */
    public function createRevision(User $user, Contract $contract): bool
    {
        // All authenticated users can create revisions of any finalized contract
        // This allows team collaboration and picking up each other's work
        return $contract->status === 'finalized';
    }
}
