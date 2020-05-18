<?php
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
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class SepaDirectDebitGuaranteedTest extends BasePaymentTest
{
    /**
     * Verify sepa direct debit guaranteed can be created with mandatory fields only.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatableWithMandatoryFieldsOnly()
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @depends sepaDirectDebitGuaranteedShouldBeCreatable
     */
    public function directDebitGuaranteedShouldProhibitAuthorization(SepaDirectDebitGuaranteed $directDebitGuaranteed)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $directDebitGuaranteed, self::RETURN_URL);
    }

    /**
     * Verify direct debit guaranteed can be charged.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function directDebitGuaranteedShouldAllowCharge()
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function ddgShouldThrowErrorIfAddressesDoNotMatch()
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($directDebitGuaranteed);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $customer = $this->getMaximumCustomerInclShippingAddress();
        $directDebitGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer);
    }
}
