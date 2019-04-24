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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration/payment_types
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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatableWithMandatoryFieldsOnly()
    {
        /** @var SepaDirectDebitGuaranteed $directDebitGuaranteed */
        $directDebitGuaranteed = new SepaDirectDebitGuaranteed('DE89370400440532013000');
        $directDebitGuaranteed = $this->heidelpay->createPaymentType($directDebitGuaranteed);
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $directDebitGuaranteed);
        $this->assertNotNull($directDebitGuaranteed->getId());

        /** @var SepaDirectDebitGuaranteed $fetchedDirectDebitGuaranteed */
        $fetchedDirectDebitGuaranteed = $this->heidelpay->fetchPaymentType($directDebitGuaranteed->getId());
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $fetchedDirectDebitGuaranteed);
        $this->assertEquals($directDebitGuaranteed->getId(), $fetchedDirectDebitGuaranteed->getId());
        $this->assertEquals(
            $this->maskNumber($directDebitGuaranteed->getIban()),
            $fetchedDirectDebitGuaranteed->getIban()
        );
    }

    /**
     * Verify sepa direct debit guaranteed can be created.
     *
     * @test
     *
     * @return SepaDirectDebitGuaranteed
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatable(): SepaDirectDebitGuaranteed
    {
        /** @var SepaDirectDebitGuaranteed $directDebitGuaranteed */
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))
            ->setHolder('John Doe')
            ->setBic('COBADEFFXXX');
        $directDebitGuaranteed = $this->heidelpay->createPaymentType($directDebitGuaranteed);
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $directDebitGuaranteed);
        $this->assertNotNull($directDebitGuaranteed->getId());

        /** @var SepaDirectDebitGuaranteed $fetchedDirectDebitGuaranteed */
        $fetchedDirectDebitGuaranteed = $this->heidelpay->fetchPaymentType($directDebitGuaranteed->getId());
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $fetchedDirectDebitGuaranteed);
        $this->assertEquals($directDebitGuaranteed->getId(), $fetchedDirectDebitGuaranteed->getId());
        $this->assertEquals($directDebitGuaranteed->getHolder(), $fetchedDirectDebitGuaranteed->getHolder());
        $this->assertEquals($directDebitGuaranteed->getBic(), $fetchedDirectDebitGuaranteed->getBic());
        $this->assertEquals(
            $this->maskNumber($directDebitGuaranteed->getIban()),
            $fetchedDirectDebitGuaranteed->getIban()
        );

        return $fetchedDirectDebitGuaranteed;
    }

    /**
     * Verify authorization is not allowed for sepa direct debit guaranteed.
     *
     * @test
     *
     * @param SepaDirectDebitGuaranteed $directDebitGuaranteed
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function directDebitGuaranteedShouldAllowCharge()
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($directDebitGuaranteed);

        $charge = $directDebitGuaranteed->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
    }

    /**
     * Verify ddg will throw error if addresses do not match.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function ddgShouldThrowErrorIfAddressesDoNotMatch()
    {
        $directDebitGuaranteed = (new SepaDirectDebitGuaranteed('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($directDebitGuaranteed);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $directDebitGuaranteed->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()
        );
    }
}
