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

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class HirePurchaseDirectDebitTest extends BasePaymentTest
{
    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    protected function childSetup()
    {
        // Hire purchase direct debit needs a different key
        $this->heidelpay->setKey('s-priv-2a10BF2Cq2YvAo6ALSGHc3X7F42oWAIp');
    }

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
        $hirePurchaseDirectDebit = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly();

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

    /**
     * Verify hire purchase direct debit can be created.
     *
     * @test
     *
     * @return HirePurchaseDirectDebit
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function hirePurchaseDirectDebitShouldBeCreatable(): HirePurchaseDirectDebit
    {
        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly();
        $hirePurchaseDirectDebit->setOrderDate('2011-04-12');
        $hirePurchaseDirectDebit = $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $hirePurchaseDirectDebit);
        $this->assertNotNull($hirePurchaseDirectDebit->getId());

        /** @var HirePurchaseDirectDebit $fetchedHirePurchaseDirectDebit */
        $fetchedHirePurchaseDirectDebit = $this->heidelpay->fetchPaymentType($hirePurchaseDirectDebit->getId());
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $fetchedHirePurchaseDirectDebit);

        $this->assertEquals($hirePurchaseDirectDebit->expose(), $fetchedHirePurchaseDirectDebit->expose());

//        $this->assertEquals($hirePurchaseDirectDebit->getId(), $fetchedHirePurchaseDirectDebit->getId());
//        $this->assertEquals($hirePurchaseDirectDebit->getAccountHolder(), $fetchedHirePurchaseDirectDebit->getAccountHolder());
//        $this->assertEquals($hirePurchaseDirectDebit->getBic(), $fetchedHirePurchaseDirectDebit->getBic());
//        $this->assertEquals(
//            $this->maskNumber($hirePurchaseDirectDebit->getIban()),
//            $fetchedHirePurchaseDirectDebit->getIban()
//        );

        return $fetchedHirePurchaseDirectDebit;
    }

    /**
     * Verify authorization is not allowed for hire purchase direct debit.
     *
     * @test
     *
     * @param HirePurchaseDirectDebit $hirePurchaseDirectDebit
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends hirePurchaseDirectDebitShouldBeCreatable
     */
    public function hirePurchaseDirectDebitShouldProhibitAuthorization(HirePurchaseDirectDebit $hirePurchaseDirectDebit)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $hirePurchaseDirectDebit, self::RETURN_URL);
    }

    /**
     * Verify direct debit guaranteed can be charged.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function hirePurchaseDirectDebitShouldAllowCharge()
    {
        $hirePurchaseDirectDebit = (new HirePurchaseDirectDebit('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);

        $charge = $hirePurchaseDirectDebit->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
    }

    /**
     * Verify hire purchase direct debit will throw error if addresses do not match.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function hddShouldThrowErrorIfAddressesDoNotMatch()
    {
        $hirePurchaseDirectDebit = (new HirePurchaseDirectDebit('DE89370400440532013000', ));
        $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $hirePurchaseDirectDebit->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()
        );
    }

    //<editor-fold desc="Helper">

    /**
     * @return HirePurchaseDirectDebit
     */
    private function getHirePurchaseDirectDebitWithMandatoryFieldsOnly(): HirePurchaseDirectDebit
    {
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
        return $hirePurchaseDirectDebit;
    }

    //</editor-fold>
}
