<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 *
 * @author emung
 */
interface Recyclable
{
    /**
     * Model recycling events.
     *
     * @return void
     */
    public static function bootRecycling();

    /**
     * Recycled Model records.
     *
     * @return MorphMany
     */
    public function recycled();
}
