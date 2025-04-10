<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Transactions;

use IFRS\Interfaces\Buys;
use IFRS\Interfaces\Clearable;
use IFRS\Models\Transaction;
use IFRS\Traits\Buying;
use IFRS\Traits\Clearing;

class SupplierBill extends Transaction implements Buys, Clearable
{
    use Buying;
    use Clearing;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    public const PREFIX = Transaction::BL;

    /**
     * Construct new ContraEntry
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited']         = true;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }
}
