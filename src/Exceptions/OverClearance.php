<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Exceptions;

use IFRS\Models\Transaction;

class OverClearance extends IFRSException
{
    /**
     * Over Clearance Exception
     *
     * @param string $assignedType
     * @param float $amount
     * @param string $message
     * @param int $code
     */
    public function __construct(string $assignedType, float $amount, ?string $message = null, ?int $code = null)
    {
        $assignedType = Transaction::getType($assignedType);

        $error = $assignedType . " Transaction amount remaining to be cleared is less than " . $amount;

        parent::__construct($error . ' ' . $message, $code);
    }
}
