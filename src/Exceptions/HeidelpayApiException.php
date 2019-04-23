<?php
/**
 * This exception is thrown whenever the api returns an error.
 *
 * Copyright (C) 2018 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/exceptions
 */
namespace heidelpayPHP\Exceptions;

use Exception;

class HeidelpayApiException extends Exception
{
    const MESSAGE = 'The payment api returned an error!';
    const CLIENT_MESSAGE = 'The payment api returned an error!';

    /** @var string $clientMessage */
    protected $clientMessage;

    /**
     * HeidelpayApiException constructor.
     *
     * @param string $merchantMessage
     * @param string $clientMessage
     * @param string $code
     */
    public function __construct($merchantMessage = '', $clientMessage = '', $code = 'No error code provided')
    {
        $merchantMessage = empty($merchantMessage) ? static::MESSAGE : $merchantMessage;
        $this->clientMessage = empty($clientMessage) ? static::CLIENT_MESSAGE : $clientMessage;
        parent::__construct($merchantMessage);
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getClientMessage(): string
    {
        return $this->clientMessage;
    }

    /**
     * @return string
     */
    public function getMerchantMessage(): string
    {
        return $this->getMessage();
    }
}
