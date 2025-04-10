<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Traits;

use IFRS\Exceptions\LineItemAccount;
use IFRS\Exceptions\MainAccount;
use IFRS\Models\Account;
use IFRS\Models\LineItem;

trait Selling
{
    /**
     * Validate Selling Transaction Main Account.
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->account) or Account::RECEIVABLE != $this->account->account_type) {
            throw new MainAccount(self::PREFIX, Account::RECEIVABLE);
        }

        return parent::save();
    }

    /**
     * Validate Selling Transaction LineItem.
     */
    public function addLineItem(LineItem $lineItem): bool
    {
        if (Account::OPERATING_REVENUE != $lineItem->account->account_type) {
            throw new LineItemAccount(self::PREFIX, [Account::OPERATING_REVENUE]);
        }

        return parent::addLineItem($lineItem);
    }
}
