<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sepa direct debit guaranteed.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\test\BaseIntegrationTest;

class SepaDirectDebitGuaranteedTest extends BaseIntegrationTest
{
    /**
     * Verify sepa direct debit guaranteed can be created with mandatory fields only.
     *
     * @test
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatableWithMandatoryFieldsOnly(): void
    {
        $directDebitGuaranteed = new SepaDirectDebitGuaranteed('DE89370400440532013000');
        /** @var SepaDirectDebitGuaranteed $directDebitGuaranteed */
        $directDebitGuaranteed = $this->heidelpay->createPaymentType($directDebitGuaranteed);
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $directDebitGuaranteed);
        $this->assertNotNull($directDebitGuaranteed->getId());

        /** @var SepaDirectDebitGuaranteed $fetchedDirectDebitGuaranteed */
        $fetchedDirectDebitGuaranteed = $this->heidelpay->fetchPaymentType($directDebitGuaranteed->getId());
        $this->assertEquals($directDebitGuaranteed->expose(), $fetchedDirectDebitGuaranteed->expose());
    }

    /**
     * Verify sepa direct debit guaranteed can be created.
     *
     * @test
     *
     * @return SepaDirectDebitGuaranteed
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatable(): SepaDirectDebitGuaranteed
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setHolder('John Doe')->setBic('COBADEFFXXX');
        /** @var SepaDirectDebitGuaranteed $directDebitGuaranteed */
        $directDebitGuaranteed = $this->heidelpay->createPaymentType($directDebitGuaranteed);
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $directDebitGuaranteed);
        $this->assertNotNull($directDebitGuaranteed->getId());

        /** @var SepaDirectDebitGuaranteed $fetchedDirectDebitGuaranteed */
        $fetchedDirectDebitGuaranteed = $this->heidelpay->fetchPaymentType($directDebitGuaranteed->getId());
        $this->assertEquals($directDebitGuaranteed->expose(), $fetchedDirectDebitGuaranteed->expose());

        return $fetchedDirectDebitGuaranteed;
    }

    /**
     * Verify authorization is not allowed for sepa direct debit guaranteed.
     *
     * @test
     *
     * @param SepaDirectDebitGuaranteed $directDebitGuaranteed
     * @depends sepaDirectDebitGuaranteedShouldBeCreatable
     */
    public function directDebitGuaranteedShouldProhibitAuthorization(SepaDirectDebitGuaranteed $directDebitGuaranteed): void
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $directDebitGuaranteed, self::RETURN_URL);
    }

    /**
     * Verify direct debit guaranteed can be charged.
     *
     * @test
     */
    public function directDebitGuaranteedShouldAllowCharge(): void
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($directDebitGuaranteed);

        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge   = $directDebitGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer);
        $this->assertTransactionResourceHasBeenCreated($charge);
    }

    /**
     * Verify ddg will throw error if addresses do not match.
     *
     * @test
     */
    public function ddgShouldThrowErrorIfAddressesDoNotMatch(): void
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($directDebitGuaranteed);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $customer = $this->getMaximumCustomerInclShippingAddress();
        $directDebitGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer);
    }
}
