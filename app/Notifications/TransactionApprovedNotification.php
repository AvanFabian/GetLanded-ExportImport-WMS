<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

/**
 * Sent when a transaction is approved.
 */
class TransactionApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;
    protected $approver;

    public function __construct($transaction, User $approver)
    {
        $this->transaction = $transaction;
        $this->approver = $approver;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = class_basename($this->transaction);
        $code = $this->transaction->transaction_code ?? $this->transaction->id;

        return (new MailMessage)
            ->subject("Transaction Approved: {$type} #{$code}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your {$type} #{$code} has been approved.")
            ->line("Approved by: {$this->approver->name}")
            ->line("Approved at: " . now()->format('d M Y H:i'))
            ->action('View Transaction', url('/'))
            ->line('Stock has been updated accordingly.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transaction_approved',
            'transaction_type' => class_basename($this->transaction),
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $this->transaction->transaction_code ?? null,
            'approver_id' => $this->approver->id,
            'approver_name' => $this->approver->name,
            'message' => class_basename($this->transaction) . ' has been approved',
        ];
    }
}
