<?php
/**
 * This class defines unit tests to verify functionality of the AbstractTransactionType.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Resources\Payment;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AbstractTransactionTypeTest extends TestCase
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function theGettersAndSettersShouldWorkProperly()
    {
        $payment = new Payment();
        $transactionType = new DummyTransactionType();
        $this->assertNull($transactionType->getPayment());
        $this->assertNull($transactionType->getDate());
        $this->assertNull($transactionType->getPaymentId());
        $this->assertNull($transactionType->getUniqueId());
        $this->assertNull($transactionType->getShortId());

        $transactionType->setPayment($payment);
        $this->assertNull($transactionType->getRedirectUrl());

        $payment->setId('MyPaymentId');
        $date = (new \DateTime('now'))->format('Y-m-d h:i:s');
        $transactionType->setPayment($payment);
        $transactionType->setDate($date);

        $this->assertSame($payment, $transactionType->getPayment());
        $this->assertEquals($date, $transactionType->getDate());
        $this->assertNull($transactionType->getExternalId());
        $this->assertEquals($payment->getId(), $transactionType->getPaymentId());
    }
}
