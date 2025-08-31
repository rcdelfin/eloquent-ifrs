<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use IFRS\Models\Assignment;

/**
 *
 * @author emung
 */
trait Clearing
{
    /**
     * Cleared Transaction amount.
     */
    public function getClearedAmountAttribute()
    {
        $cleared = 0;
        $this->load('clearances');

        foreach ($this->clearances as $clearance) {
            $cleared += $clearance->amount;
        }

        return $cleared;
    }

    /**
     * Cleared Model records.
     *
     * @return MorphMany
     */
    public function clearances()
    {
        return $this->morphMany(Assignment::class, 'cleared');
    }
}
