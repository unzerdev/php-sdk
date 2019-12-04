<?php
/**
 * This class defines unit tests to verify functionality of the Recurring resource.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\Recurring;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class RecurringTest extends BasePaymentTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $recurring = new Recurring('payment type id', $this::RETURN_URL);
        $recurring->setPayment(new Payment());
        $this->assertEquals('payment type id', $recurring->getPaymentTypeId());
        $this->assertEquals($this::RETURN_URL, $recurring->getReturnUrl());

        $recurring->handleResponse((object)['redirectUrl' => 'redirect url']);
        $this->assertEquals('redirect url', $recurring->getRedirectUrl());
        $recurring->handleResponse((object)['redirectUrl' => 'different redirect url']);
        $this->assertEquals('different redirect url', $recurring->getRedirectUrl());

        $recurring->setPaymentTypeId('another type id');
        $this->assertEquals('another type id', $recurring->getPaymentTypeId());

        $recurring->setReturnUrl('another Return url');
        $this->assertEquals('another Return url', $recurring->getReturnUrl());
    }
}
