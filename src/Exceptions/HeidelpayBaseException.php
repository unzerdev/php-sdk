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

class HeidelpayBaseException extends \RuntimeException
{
    const MESSAGE = 'Exception message not set!';

    /**
     * IdRequiredToFetchResourceException constructor.
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        if (empty($message)) {
            $message = static::MESSAGE;
        }

        parent::__construct($message);
    }
}
