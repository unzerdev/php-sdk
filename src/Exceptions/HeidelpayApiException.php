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

class HeidelpayApiException extends HeidelpayBaseException
{
    const MESSAGE = 'The payment api returned an error!';

    /**
     * HeidelpayApiException constructor.
     * @param string $merchantMessage
     * @param string $customerMessage
     * @param string $code
     */
    public function __construct($merchantMessage = '', $customerMessage = '', $code = '')
    {
        parent::__construct($merchantMessage, $customerMessage);
        $this->code = $code;
    }


}
