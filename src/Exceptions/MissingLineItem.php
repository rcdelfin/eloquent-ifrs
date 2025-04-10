<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Exceptions;

class MissingLineItem extends IFRSException
{
    /**
     * Missing Line Item Exception
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(?string $message = null, ?int $code = null)
    {
        $error = "A Transaction must have at least one LineItem to be posted ";

        parent::__construct($error . $message, $code = null);
    }
}
