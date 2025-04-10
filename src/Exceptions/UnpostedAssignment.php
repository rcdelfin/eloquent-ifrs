<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Exceptions;

class UnpostedAssignment extends IFRSException
{
    /**
     * Unposted Assignment Exception
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(?string $message = null, ?int $code = null)
    {
        $error = "An Unposted Transaction cannot be Assigned or Cleared ";

        parent::__construct($error . $message, $code);
    }
}
