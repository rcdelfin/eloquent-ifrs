<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Models;

use Carbon\Carbon;
use IFRS\Interfaces\Recyclable;
use IFRS\Interfaces\Segregatable;
use IFRS\Traits\ModelTablePrefix;
use IFRS\Traits\Recycling;
use IFRS\Traits\Segregating;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CostCenter
 *
 * @package Ekmungai\Eloquent-IFRS
 *
 * @property Entity $entity
 * @property string $code
 * @property string $name
 * @property string $description
 * @property bool $active
 * @property Carbon $destroyed_at
 * @property Carbon $deleted_at
 */
class CostCenter extends Model implements Segregatable, Recyclable
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
        'code',
        'name',
        'description',
        'active',
        'entity_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Instance Identifier.
     *
     * @return string
     */
    public function toString($type = false)
    {
        $classname = explode('\\', self::class);
        return $type ? array_pop($classname) . ': ' . $this->name : $this->name;
    }

    /**
     * Cost Center Accounts.
     *
     * @return HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * CostCenter attributes.
     *
     * @return object
     */
    public function attributes()
    {
        return (object) $this->attributes;
    }

    /**
     * Validate CostCenter.
     */
    public function save(array $options = []): bool
    {
        $this->name = ucfirst($this->name);
        return parent::save($options);
    }
}
