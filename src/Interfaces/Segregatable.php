<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Interfaces;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 * @author emung
 */
interface Segregatable
{
    /**
     * Register EntityScope for Model.
     *
     * @return null
     */
    public static function bootSegregating();

    /**
     * Model's Parent Entity.
     *
     * @return HasOne
     */
    public function entity();
}
