<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Transactions;

use IFRS\Interfaces\Assignable;
use IFRS\Interfaces\Sells;
use IFRS\Models\Transaction;
use IFRS\Traits\Assigning;
use IFRS\Traits\Selling;

class CreditNote extends Transaction implements Sells, Assignable
{
    use Assigning;
    use Selling;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    public const PREFIX = Transaction::CN;

    /**
     * Construct new ContraEntry
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited'] = true;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }
}
