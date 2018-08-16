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

    /** @var string $clientMessage */
    protected $clientMessage;

    /**
     * IdRequiredToFetchResourceException constructor.
     *
     * @param string $customerMessage
     * @param string $merchantMessage
     */
    public function __construct($merchantMessage = '', $customerMessage = '')
    {
        $merchantMessage = empty($merchantMessage) ? static::MESSAGE : $merchantMessage;
        $this->clientMessage = empty($customerMessage) ? $merchantMessage : $customerMessage;

        parent::__construct($merchantMessage);
    }
}
