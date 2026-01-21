<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a user tries to approve their own transaction.
 */
class SelfApprovalException extends Exception
{
    public function __construct(string $message = 'You cannot approve your own transaction')
    {
        parent::__construct($message);
    }
}
