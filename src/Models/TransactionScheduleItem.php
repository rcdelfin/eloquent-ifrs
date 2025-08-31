<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    GitHub Copilot
 * @copyright ReadAhead, 2025, Philippines
 * @license   MIT
 */

namespace IFRS\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use IFRS\Exceptions\InvalidCurrency;
use IFRS\Exceptions\MissingAccountType;
use IFRS\Traits\ModelTablePrefix;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionScheduleItem
 *
 * @package IFRS\Models
 *
 * @property TransactionSchedule $schedule
 * @property Entity $entity
 * @property Account $account
 * @property Account $receivableAccount
 * @property Currency $currency
 * @property float $amount
 */
class TransactionScheduleItem extends Model
{
    use ModelTablePrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_id',
        'account_id',
        'receivable_account_id',
        'currency_id',
        'amount',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
    ];

    /**
     * Transaction Schedule relationship.
     *
     * @return BelongsTo
     */
    public function schedule()
    {
        return $this->belongsTo(TransactionSchedule::class, 'schedule_id');
    }

    /**
     * Account relationship.
     *
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Receivable Account relationship.
     *
     * @return BelongsTo
     */
    public function receivableAccount()
    {
        return $this->belongsTo(Account::class, 'receivable_account_id');
    }

    /**
     * Currency relationship.
     *
     * @return BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Transaction Schedule Item attributes.
     *
     * @return object
     */
    public function attributes()
    {
        return (object) $this->attributes;
    }

    /**
     * Validate TransactionScheduleItem.
     */
    public function save(array $options = []): bool
    {
        // Validate receivable account type
        if ($this->receivable_account_id) {
            $receivableAccount = Account::find($this->receivable_account_id);
            if ($receivableAccount && Account::RECEIVABLE != $receivableAccount->account_type) {
                throw new MissingAccountType('Receivable account must be of type RECEIVABLE');
            }
        }

        // Validate currency compatibility
        if (
            isset($this->account_id, $this->currency_id)
             &&
            ($account = Account::find($this->account_id)) &&
            in_array($account->account_type, config('ifrs.single_currency')) &&
            $account->currency_id != $this->currency_id
        ) {
            throw new InvalidCurrency("TransactionScheduleItem", $account);
        }

        return parent::save($options);
    }
}
