<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

/**
 * Sent when a transaction is rejected.
 */
class TransactionRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;
    protected $rejector;
    protected $reason;

    public function __construct($transaction, User $rejector, string $reason)
    {
        $this->transaction = $transaction;
        $this->rejector = $rejector;
        $this->reason = $reason;
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
            ->subject("Transaction Rejected: {$type} #{$code}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your {$type} #{$code} has been rejected.")
            ->line("Rejected by: {$this->rejector->name}")
            ->line("Reason: {$this->reason}")
            ->action('View Transaction', url('/'))
            ->line('Please review and resubmit if necessary.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transaction_rejected',
            'transaction_type' => class_basename($this->transaction),
            'transaction_id' => $this->transaction->id,
            'transaction_code' => $this->transaction->transaction_code ?? null,
            'rejector_id' => $this->rejector->id,
            'rejector_name' => $this->rejector->name,
            'reason' => $this->reason,
            'message' => class_basename($this->transaction) . ' was rejected: ' . $this->reason,
        ];
    }
}
