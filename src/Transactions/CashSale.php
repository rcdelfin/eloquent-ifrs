<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Transactions;

use IFRS\Exceptions\MainAccount;
use IFRS\Interfaces\Sells;
use IFRS\Models\Account;
use IFRS\Models\Transaction;
use IFRS\Traits\Selling;

class CashSale extends Transaction implements Sells
{
    use Selling;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    public const PREFIX = Transaction::CS;

    /**
     * Construct new CashSale
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited']         = false;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }

    /**
     * Validate CashSale Main Account
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->account) || Account::BANK != $this->account->account_type) {
            throw new MainAccount(self::PREFIX, Account::BANK);
        }

        return parent::save();
    }
}
