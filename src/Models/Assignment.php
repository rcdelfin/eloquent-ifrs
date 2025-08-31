<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use IFRS\Exceptions\InsufficientBalance;
use IFRS\Exceptions\InvalidClearanceAccount;
use IFRS\Exceptions\InvalidClearanceCurrency;
use IFRS\Exceptions\InvalidClearanceEntry;
use IFRS\Exceptions\InvalidTransaction;
use IFRS\Exceptions\MissingForexAccount;
use IFRS\Exceptions\MixedAssignment;
use IFRS\Exceptions\NegativeAmount;
use IFRS\Exceptions\OverClearance;
use IFRS\Exceptions\SelfClearance;
use IFRS\Exceptions\UnassignableTransaction;
use IFRS\Exceptions\UnclearableTransaction;
use IFRS\Exceptions\UnpostedAssignment;
use IFRS\Interfaces\Assignable;
use IFRS\Interfaces\Segregatable;
use IFRS\Reports\AccountSchedule;
use IFRS\Traits\ModelTablePrefix;
use IFRS\Traits\Segregating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Assignment
 *
 * @package Ekmungai\Eloquent-IFRS
 *
 * @property Entity $entity
 * @property Assignable $transaction
 * @property Clearable $cleared
 * @property float $amount
 * @property Carbon $destroyed_at
 * @property Carbon $deleted_at
 */
class Assignment extends Model implements Segregatable
{
    use ModelTablePrefix;
    use Segregating;
    use SoftDeletes;

    /**
     * Clearable Transaction Types
     *
     * @var array
     */

    public const CLEARABLES = [
        Transaction::IN,
        Transaction::BL,
        Transaction::JN,
    ];

    /**
     * Assignable Transaction Types
     *
     * @var array
     */

    public const ASSIGNABLES = [
        Transaction::RC,
        Transaction::PY,
        Transaction::CN,
        Transaction::DN,
        Transaction::JN,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'assignment_date',
        'transaction_id',
        'cleared_id',
        'cleared_type',
        'entity_id',
        'forex_account_id',
        'amount',
    ];

    /**
     * Bulk assign a transaction to outstanding Transactions, under FIFO (First in first out) methodology
     *
     * @param Assignable $transaction
     *
     * @return void
     */

    public static function bulkAssign(Assignable $transaction): void
    {

        $balance = $transaction->balance;

        $schedule = new AccountSchedule($transaction->account_id, $transaction->currency_id);
        $schedule->getTransactions();

        foreach ($schedule->transactions as $outstanding) {
            $assignment = new Assignment([
                'assignment_date' => Carbon::now(),
                'transaction_id' => $transaction->id,
                'cleared_id' => $outstanding->id,
                'cleared_type' => $outstanding->cleared_type,
            ]);

            if ($outstanding->unclearedAmount > $balance) {
                $assignment->amount = $balance;
                $assignment->save();
                break;
            } else {
                $assignment->amount = $outstanding->unclearedAmount;
                $assignment->save();

                $balance -= $outstanding->unclearedAmount;
            }
        }
    }

    /**
     * Assignment Validation.
     */
    public function save(array $options = []): bool
    {
        $transactionType = $this->transaction->transaction_type;
        $clearedType = $this->cleared->transaction_type;

        $transactionRate = $this->transaction->exchangeRate->rate;
        $clearedRate = $this->cleared->exchangeRate->rate;

        $this->validate($transactionRate, $clearedRate, $transactionType, $clearedType);

        // Realize Forex differences
        if (0 == !bccomp($transactionRate, $clearedRate, config('ifrs.forex_scale'))) {
            Ledger::postForex($this, $transactionRate, $clearedRate);
        }
        return parent::save();
    }

    /**
     * Assignment Validation.
     *
     * @param float $transactionRate
     * @param float $clearedRate
     * @param string $transactionType
     * @param string $clearedType
     *
     * @return void
     */
    private function validate(float $transactionRate, float $clearedRate, string $transactionType, string $clearedType): void
    {
        if (!in_array($transactionType, Assignment::ASSIGNABLES)) {
            throw new UnassignableTransaction($transactionType, Assignment::ASSIGNABLES);
        }

        // Clearable Transactions
        if (!in_array($clearedType, Assignment::CLEARABLES)) {
            throw new UnclearableTransaction($clearedType, Assignment::CLEARABLES);
        }

        if ($this->amount < 0) {
            throw new NegativeAmount("Assignment");
        }

        if ($this->cleared_id == $this->transaction_id && Transaction::MODELNAME == $this->cleared_type) {
            throw new SelfClearance();
        }

        if (!$this->transaction->is_posted || !$this->cleared->is_posted) {
            throw new UnpostedAssignment();
        }

        if ($this->cleared->account_id != $this->transaction->account_id) {
            throw new InvalidClearanceAccount();
        }

        if ($this->cleared->currency_id != $this->transaction->currency_id) {
            throw new InvalidClearanceCurrency();
        }

        if ($this->cleared->is_credited == $this->transaction->is_credited) {
            throw new InvalidClearanceEntry();
        }

        if (round($this->transaction->balance, config('ifrs.forex_scale')) < round($this->amount, config('ifrs.forex_scale'))) {
            throw new InsufficientBalance($transactionType, $this->amount, $clearedType);
        }

        if (-1 == bccomp($this->cleared->amount - $this->cleared->cleared_amount, $this->amount)) {
            throw new OverClearance($clearedType, $this->amount);
        }

        if (0 == !bccomp($transactionRate, $clearedRate, config('ifrs.forex_scale')) && !isset($this->forex_account_id)) {
            throw new MissingForexAccount();
        }

        if (Balance::MODELNAME != $this->cleared_type && count($this->cleared->assignments) > 0) {
            throw new MixedAssignment("Assigned", "Cleared");
        }

        if (count($this->transaction->clearances) > 0) {
            throw new MixedAssignment("Cleared", "Assigned");
        }

        if ($this->transaction->compound || $this->cleared->compound) {
            throw new InvalidTransaction();
        }
    }

    /**
     * Instance Identifier.
     *
     * @return string
     */
    public function toString($type = false): string
    {
        $classname = explode('\\', self::class);
        $description = 'Assigning ' . $this->transaction->transaction_no . ' on ' . $this->assignment_date;
        return $type ? array_pop($classname) . ': ' . $description : $description;
    }

    /**
     * Transaction to be cleared.
     *
     * @return BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Transaction|Balance to be cleared.
     *
     * @return MorphTo
     */
    public function cleared()
    {
        return $this->morphTo();
    }

    /**
     * Account for posting Exchange Rate Differences.
     *
     * @return HasOne
     */
    public function forexAccount()
    {
        return $this->hasOne(Account::class);
    }

    /**
     * Assignment attributes.
     *
     * @return object
     */
    public function attributes(): object
    {
        return (object) $this->attributes;
    }
}
