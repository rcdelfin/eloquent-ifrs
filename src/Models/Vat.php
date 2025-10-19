<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Models;

use IFRS\Exceptions\InvalidAccountType;
use IFRS\Exceptions\MissingVatAccount;
use IFRS\Interfaces\Recyclable;
use IFRS\Interfaces\Segregatable;
use IFRS\Traits\ModelTablePrefix;
use IFRS\Traits\Recycling;
use IFRS\Traits\Segregating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Vat
 *
 * @package Ekmungai\Eloquent-IFRS
 *
 * @property Entity $entity
 * @property Account $account
 * @property string $code
 * @property string $name
 * @property float $rate
 * @property Carbon $destroyed_at
 * @property Carbon $deleted_at
 */
class Vat extends Model implements Segregatable, Recyclable
{
    use ModelTablePrefix;
    use Recycling;
    use Segregating;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'rate',
        'valid_from',
        'account_id',
        'valid_to',
        'entity_id',
    ];

    /**
     * Instance Identifier.
     *
     * @return string
     */
    public function toString($type = false): string
    {
        $classname = explode('\\', self::class);
        $description = $this->name . ' (' . $this->code . ') at ' . number_format($this->rate, 2) . '%';
        return $type ? array_pop($classname) . ': ' . $description : $description;
    }

    /**
     * Vat attributes.
     *
     * @return object
     */
    public function attributes(): object
    {
        return (object) $this->attributes;
    }

    /**
     * LineItem Vat Account.
     *
     * @return HasOne
     */
    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    /**
     * Vat Validation.
     */
    public function save(array $options = []): bool
    {
        if (!is_null($this->rate)) {
            $this->rate = abs($this->rate);
        }

        if (0 == $this->rate) {
            $this->account_id = null;
        }

        if ($this->rate > 0 && is_null($this->account_id)) {
            throw new MissingVatAccount($this->rate);
        }

        if ($this->rate > 0 && Account::CONTROL != $this->account->account_type) {
            throw new InvalidAccountType('Vat', Account::CONTROL);
        }

        return parent::save();
    }
}
