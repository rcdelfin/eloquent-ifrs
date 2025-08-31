<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Ezeugwu Paschal
 * @copyright Ezeugwu Paschal, 2020, Nigeria
 * @license   MIT
 */

namespace IFRS\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use IFRS\Models\Entity;

trait IFRSUser
{
    /**
     * User's Parent Entity.
     *
     * @return BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * User attributes.
     *
     * @return object
     */
    public function attributes()
    {
        return (object) $this->attributes;
    }
}
