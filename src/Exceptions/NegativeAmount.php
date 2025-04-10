<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Exceptions;

class NegativeAmount extends IFRSException
{
    /**
     * Negative Amount Exception
     *
     * @param string $modelType
     * @param string $message
     * @param int $code
     */
    public function __construct(string $modelType, ?string $message = null, ?int $code = null)
    {
        $error = $modelType . " Amount cannot be negative ";

        parent::__construct($error . $message, $code);
    }
}
