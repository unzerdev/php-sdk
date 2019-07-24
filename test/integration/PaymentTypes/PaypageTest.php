<?php
/**
 * This class defines integration tests to verify interface and functionality of the Paypage.
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
 * @package  heidelpayPHP/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;

class PaypageTest extends BasePaymentTest
{
    /**
     * Verify the Paypage resource can be created and fetched with the mandatory parameters only.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function minimalPaypageShouldBeCreatableAndFetchable()
    {
        $paypage = new Paypage(100.0, 'EUR', self::RETURN_URL);
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->createPaymentType($paypage);
        $this->assertNotEmpty($paypage->getId());
    }
}
