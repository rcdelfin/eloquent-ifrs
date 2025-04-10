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
use IFRS\Interfaces\Buys;
use IFRS\Models\Transaction;
use IFRS\Traits\Assigning;
use IFRS\Traits\Buying;

class DebitNote extends Transaction implements Buys, Assignable
{
    use Assigning;
    use Buying;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    public const PREFIX = Transaction::DN;

    /**
     * Construct new ContraEntry
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited']         = false;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }
}
