<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class SelfApprovalGuard
{
    /**
     * Check if a user can self-approve a transaction
     *
     * @param User $user The user attempting approval
     * @param Model $transaction The transaction being approved
     * @return array ['allowed' => bool, 'reason' => string|null, 'is_self_approval' => bool]
     */
    public function canSelfApprove(User $user, Model $transaction): array
    {
        // Check if this is actually a self-approval attempt
        $creatorId = $transaction->created_by ?? $transaction->creator_id ?? null;
        $isSelfApproval = $creatorId === $user->id;

        if (!$isSelfApproval) {
            return [
                'allowed' => true,
                'reason' => null,
                'is_self_approval' => false,
            ];
        }

        // Get company policy
        $company = $user->company ?? Company::find($user->company_id);
        $requireApprovalWorkflow = $company?->require_approval_workflow ?? true;

        // If approval workflow is disabled, allow self-approval
        if (!$requireApprovalWorkflow) {
            return [
                'allowed' => true,
                'reason' => 'Self-approval allowed (approval workflow disabled)',
                'is_self_approval' => true,
            ];
        }

        // Check if user is Owner/Admin (special privilege)
        if ($user->isAdmin() || $user->role === 'owner') {
            // Owners can always self-approve if policy allows
            return [
                'allowed' => true,
                'reason' => 'Self-approval allowed (admin privilege)',
                'is_self_approval' => true,
            ];
        }

        // Normal workflow: self-approval not allowed
        return [
            'allowed' => false,
            'reason' => 'Self-approval not allowed. Please have another user approve this transaction.',
            'is_self_approval' => true,
        ];
    }

    /**
     * Check if approval workflow is enabled for a company
     */
    public function isApprovalWorkflowEnabled(?int $companyId = null): bool
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        $company = Company::find($companyId);
        
        return $company?->require_approval_workflow ?? true;
    }

    /**
     * Get available approval actions for a user on a transaction
     */
    public function getAvailableActions(User $user, Model $transaction): array
    {
        $checkResult = $this->canSelfApprove($user, $transaction);
        
        $actions = [];

        // View is always available
        $actions['view'] = true;

        // Approve action
        if ($checkResult['allowed']) {
            $actions['approve'] = true;
            $actions['approve_note'] = $checkResult['reason'];
        } else {
            $actions['approve'] = false;
            $actions['approve_disabled_reason'] = $checkResult['reason'];
        }

        // Reject is typically available if approve is
        $actions['reject'] = $checkResult['allowed'];

        return $actions;
    }

    /**
     * Log a self-approval event for audit trail
     */
    public function logSelfApproval(User $user, Model $transaction, string $action = 'approved'): void
    {
        $creatorId = $transaction->created_by ?? $transaction->creator_id ?? null;
        $isSelfApproval = $creatorId === $user->id;

        if ($isSelfApproval) {
            // Log to security log
            \App\Models\SecurityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'self_approval',
                'severity' => 'info',
                'details' => [
                    'transaction_type' => get_class($transaction),
                    'transaction_id' => $transaction->id,
                    'action' => $action,
                    'created_by' => $creatorId,
                    'approved_by' => $user->id,
                    'policy_status' => $this->isApprovalWorkflowEnabled($user->company_id) 
                        ? 'enabled' : 'disabled',
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
