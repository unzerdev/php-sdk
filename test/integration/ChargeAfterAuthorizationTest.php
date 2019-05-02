<?php
/**
 * This class defines integration tests to verify charge after authorization.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class ChargeAfterAuthorizationTest extends BasePaymentTest
{
    /**
     * Validate full charge after authorization.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizationShouldBeFullyChargeable()
    {
        $authorization = $this->createAuthorization();
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100, 0, 100, 0);
        $this->assertTrue($payment->isPending());

        $charge = $authorization->charge();
        $this->heidelpay->fetchPayment($payment);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertAmounts($payment, 0, 100, 100, 0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * Validate full charge after authorization.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizationShouldBeFullyChargeableViaHeidelpayObject()
    {
        $authorization = $this->createAuthorization();
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100, 0, 100, 0);
        $this->assertTrue($payment->isPending());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId());
        $this->heidelpay->fetchPayment($payment);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertAmounts($payment, 0, 100, 100, 0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * Verify authorization is partly chargeable.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizationShouldBePartlyChargeable()
    {
        $authorization = $this->createAuthorization();
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100, 0, 100, 0);
        $this->assertTrue($payment->isPending());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 10);
        $this->heidelpay->fetchPayment($payment);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertAmounts($payment, 90, 10, 100, 0);
        $this->assertTrue($payment->isPartlyPaid());
    }
}
