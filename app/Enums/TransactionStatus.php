<?php

namespace App\Enums;

/**
 * TransactionStatus Enum
 * 
 * Used for maker-checker workflow on stock transactions.
 */
enum TransactionStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
        };
    }

    /**
     * Get badge color class.
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING_APPROVAL => 'yellow',
            self::COMPLETED => 'green',
            self::REJECTED => 'red',
        };
    }

    /**
     * Check if status allows editing.
     */
    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING_APPROVAL, self::REJECTED]);
    }

    /**
     * Check if status allows approval.
     */
    public function canApprove(): bool
    {
        return $this === self::PENDING_APPROVAL;
    }
}
