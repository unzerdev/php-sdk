<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

namespace heidelpay\NmgPhpSdk\Exceptions;

class IllegalTransactionTypeException extends HeidelpayBaseException
{
    const MESSAGE = 'Transaction type %s is not allowed!';

    /**
     * IllegalTransactionTypeException constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        parent::__construct(sprintf(self::MESSAGE, $type));
    }
}
