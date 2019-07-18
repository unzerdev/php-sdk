<?php
/**
 * This class defines integration tests to verify payout transactions.
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
 * @package  heidelpayPHP/test/integration/transaction_types
 */
namespace heidelpayPHP\test\integration\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class PayoutTest extends BasePaymentTest
{
    /**
     * Verify payout can be performed using the id of a payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function payoutCanBeCalledForCardType()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $payout = $card->payout(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotEmpty($payout->getId());
        $this->assertNotEmpty($payout->getUniqueId());
        $this->assertNotEmpty($payout->getShortId());
        $this->assertInstanceOf(Payment::class, $payout->getPayment());
        $this->assertNotEmpty($payout->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
    }
}
