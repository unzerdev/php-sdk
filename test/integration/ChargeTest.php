<?php
/**
 * This class defines integration tests to verify charges in general.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class ChargeTest extends BasePaymentTest
{
    /**
     * Verify charge can be performed using the id of a payment type.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldWorkWithTypeId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, Currencies::EURO, $card->getId(), self::RETURN_URL);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getUniqueId());
        $this->assertNotEmpty($charge->getShortId());
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }

    /**
     * Verify charging with payment type.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldWorkWithTypeObject()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, Currencies::EURO, $card, self::RETURN_URL);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }
}
