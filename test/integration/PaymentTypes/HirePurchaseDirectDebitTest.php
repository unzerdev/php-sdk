<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method hire purchase direct debit.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class HirePurchaseDirectDebitTest extends BasePaymentTest
{
    /**
     * Verify hire purchase direct debit can be created with mandatory fields only.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function hirePurchaseDirectDebitShouldBeCreatableWithMandatoryFieldsOnly()
    {
        $this->heidelpay->setKey('s-priv-2a10BF2Cq2YvAo6ALSGHc3X7F42oWAIp');

        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = new HirePurchaseDirectDebit(
            'DE46940594210000012345',
            'JASDFKJLKJD',
            'Khang Vu',
            3,
            '2019-04-25',
            200,
            0.97,
            200.97,
            5.99,
            2.9550,
            0,
            0,
            66.9,
            67.17
        );

        $hirePurchaseDirectDebit = $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $hirePurchaseDirectDebit);
        $this->assertNotNull($hirePurchaseDirectDebit->getId());

        /** @var HirePurchaseDirectDebit $fetchedHirePurchaseDirectDebit */
        $fetchedHirePurchaseDirectDebit = $this->heidelpay->fetchPaymentType($hirePurchaseDirectDebit->getId());
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $fetchedHirePurchaseDirectDebit);
        $this->assertEquals($hirePurchaseDirectDebit->getId(), $fetchedHirePurchaseDirectDebit->getId());
        $this->assertEquals(
            $this->maskNumber($hirePurchaseDirectDebit->getIban()),
            $fetchedHirePurchaseDirectDebit->getIban()
        );
    }
}
