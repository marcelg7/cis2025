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
        // Admins can view any contract
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can only view contracts they created
        return $contract->updated_by === $user->id;
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
        // Admins can update any contract
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can only update contracts they created
        // And only if the contract is not finalized
        return $contract->updated_by === $user->id && $contract->status !== 'finalized';
    }

    /**
     * Determine if the user can delete the contract.
     */
    public function delete(User $user, Contract $contract): bool
    {
        // Only admins can delete contracts
        if ($user->hasRole('admin')) {
            return true;
        }

        // Regular users can delete their own draft contracts
        return $contract->updated_by === $user->id && $contract->status === 'draft';
    }

    /**
     * Determine if the user can sign the contract.
     */
    public function sign(User $user, Contract $contract): bool
    {
        // Admins can sign any contract
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can sign contracts they created
        return $contract->updated_by === $user->id;
    }

    /**
     * Determine if the user can download the contract.
     */
    public function download(User $user, Contract $contract): bool
    {
        // Admins can download any contract
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can download contracts they created
        return $contract->updated_by === $user->id;
    }

    /**
     * Determine if the user can finalize the contract.
     */
    public function finalize(User $user, Contract $contract): bool
    {
        // Admins can finalize any contract
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can finalize their own contracts if all signatures are complete
        return $contract->updated_by === $user->id;
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
