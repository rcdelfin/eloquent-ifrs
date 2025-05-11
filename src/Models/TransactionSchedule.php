<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    GitHub Copilot
 * @copyright ReadAhead, 2025, Philippines
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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TransactionSchedule
 *
 * @package IFRS\Models
 *
 * @property Entity $entity
 * @property string $name
 * @property string $description
 * @property string $frequency
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property boolean $is_active
 * @property boolean $is_template
 * @property Carbon $destroyed_at
 * @property Carbon $deleted_at
 */
class TransactionSchedule extends Model implements Recyclable, Segregatable
{
    use ModelTablePrefix;
    use Recycling;
    use Segregating;
    use SoftDeletes;

    /**
     * Transaction Schedule Frequency Types
     *
     * @var string
     */
    public const DAILY = 'DAILY';
    public const WEEKLY = 'WEEKLY';
    public const BIWEEKLY = 'BIWEEKLY';
    public const MONTHLY = 'MONTHLY';
    public const QUARTERLY = 'QUARTERLY';
    public const SEMESTERLY = 'SEMESTERLY';
    public const ANNUALLY = 'ANNUALLY';

    /**
     * Available Frequencies
     *
     * @var array
     */
    public const FREQUENCIES = [
        self::DAILY,
        self::WEEKLY,
        self::BIWEEKLY,
        self::MONTHLY,
        self::QUARTERLY,
        self::SEMESTERLY,
        self::ANNUALLY,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_id',
        'name',
        'description',
        'frequency',
        'start_date',
        'end_date',
        'is_active',
        'is_template',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_template' => 'boolean',
    ];

    /**
     * Get Human Readable Frequency Type.
     *
     * @param string $frequency
     *
     * @return string
     */
    public static function getFrequency($frequency)
    {
        return config('ifrs')['frequencies'][$frequency] ?? $frequency;
    }

    /**
     * Instance Type Translator.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return TransactionSchedule::getFrequency($this->frequency);
    }

    /**
     * Instance Identifier.
     *
     * @return string
     */
    public function toString($type = false)
    {
        return $type ? $this->type . ': ' . $this->name : $this->name;
    }

    /**
     * Transaction Schedule Items relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(TransactionScheduleItem::class, 'schedule_id');
    }

    /**
     * Entity relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Transaction Schedule attributes.
     *
     * @return object
     */
    public function attributes()
    {
        return (object) $this->attributes;
    }

    /**
     * Generate the next transaction dates based on the schedule
     *
     * @param Carbon|null $startFrom
     * @param int|null $limit
     *
     * @return array
     */
    public function generateScheduledDates(?Carbon $startFrom = null, ?int $limit = null): array
    {
        $dates = [];
        $currentDate = $startFrom ?? $this->start_date;
        $endDate = $this->end_date;
        $count = 0;

        while ($currentDate <= $endDate) {
            if (null !== $limit && $count >= $limit) {
                break;
            }

            $dates[] = $currentDate->copy();
            $currentDate = $this->getNextDate($currentDate);
            $count++;
        }

        return $dates;
    }

    /**
     * Calculate the next date based on frequency
     *
     * @param Carbon $currentDate
     *
     * @return Carbon
     */
    protected function getNextDate(Carbon $currentDate): Carbon
    {
        $nextDate = $currentDate->copy();

        switch ($this->frequency) {
            case self::DAILY:
                return $nextDate->addDay();
            case self::WEEKLY:
                return $nextDate->addWeek();
            case self::BIWEEKLY:
                return $nextDate->addWeeks(2);
            case self::MONTHLY:
                return $nextDate->addMonth();
            case self::QUARTERLY:
                return $nextDate->addMonths(3);
            case self::SEMESTERLY:
                return $nextDate->addMonths(6);
            case self::ANNUALLY:
                return $nextDate->addYear();
            default:
                return $nextDate->addMonth();
        }
    }

    /**
     * Generate transaction templates for a given date range
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     *
     * @return array
     */
    public function generateTransactionTemplates(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now();
        $endDate = $endDate ?? $startDate->copy()->addMonths(1);

        $dates = $this->generateScheduledDates($startDate, null);
        $templates = [];

        foreach ($dates as $date) {
            if ($date >= $startDate && $date <= $endDate) {
                foreach ($this->items as $item) {
                    $templates[] = $this->createTransactionTemplate($date, $item);
                }
            }
        }

        return $templates;
    }

    /**
     * Create a transaction template for a specific date and item
     *
     * @param Carbon $date
     * @param TransactionScheduleItem $item
     *
     * @return array
     */
    protected function createTransactionTemplate(Carbon $date, TransactionScheduleItem $item): array
    {
        $currencyId = $item->currency_id;

        // If currency_id is null, try to get from entity's default currency
        if (!$currencyId && $this->entity) {
            $currencyId = $this->entity->currency_id;
        }

        return [
            'account_id' => $item->receivable_account_id,
            'transaction_date' => $date,
            'currency_id' => $currencyId,
            'amount' => $item->amount,
            'narration' => $this->name . ' - ' . $date->format('M d, Y'),
            'line_items' => [
                [
                    'account_id' => $item->account_id,
                    'amount' => $item->amount,
                    'description' => $item->description ?? $this->description,
                ],
            ],
        ];
    }

    /**
     * Validate TransactionSchedule.
     */
    public function save(array $options = []): bool
    {
        if (!isset($this->is_active)) {
            $this->is_active = true;
        }

        if (!isset($this->is_template)) {
            $this->is_template = false;
        }

        return parent::save($options);
    }
}
