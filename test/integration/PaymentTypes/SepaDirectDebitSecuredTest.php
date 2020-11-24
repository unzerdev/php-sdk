<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sepa direct debit secured.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured;
use UnzerSDK\test\BaseIntegrationTest;

class SepaDirectDebitSecuredTest extends BaseIntegrationTest
{
    /**
     * Verify sepa direct debit secured can be created with mandatory fields only.
     *
     * @test
     */
    public function sepaDirectDebitSecuredShouldBeCreatableWithMandatoryFieldsOnly(): void
    {
        $directDebitSecured = new SepaDirectDebitSecured('DE89370400440532013000');
        /** @var SepaDirectDebitSecured $directDebitSecured */
        $directDebitSecured = $this->unzer->createPaymentType($directDebitSecured);
        $this->assertInstanceOf(SepaDirectDebitSecured::class, $directDebitSecured);
        $this->assertNotNull($directDebitSecured->getId());

        /** @var SepaDirectDebitSecured $fetchedDirectDebitSecured */
        $fetchedDirectDebitSecured = $this->unzer->fetchPaymentType($directDebitSecured->getId());
        $this->assertEquals($directDebitSecured->expose(), $fetchedDirectDebitSecured->expose());
    }

    /**
     * Verify sepa direct debit secured can be created.
     *
     * @test
     *
     * @return SepaDirectDebitSecured
     */
    public function sepaDirectDebitSecuredShouldBeCreatable(): SepaDirectDebitSecured
    {
        $directDebitSecured = (new SepaDirectDebitSecured('DE89370400440532013000'))->setHolder('John Doe')->setBic('COBADEFFXXX');
        /** @var SepaDirectDebitSecured $directDebitSecured */
        $directDebitSecured = $this->unzer->createPaymentType($directDebitSecured);
        $this->assertInstanceOf(SepaDirectDebitSecured::class, $directDebitSecured);
        $this->assertNotNull($directDebitSecured->getId());

        /** @var SepaDirectDebitSecured $fetchedDirectDebitSecured */
        $fetchedDirectDebitSecured = $this->unzer->fetchPaymentType($directDebitSecured->getId());
        $this->assertEquals($directDebitSecured->expose(), $fetchedDirectDebitSecured->expose());

        return $fetchedDirectDebitSecured;
    }

    /**
     * Verify authorization is not allowed for sepa direct debit secured.
     *
     * @test
     *
     * @param SepaDirectDebitSecured $directDebitSecured
     * @depends sepaDirectDebitSecuredShouldBeCreatable
     */
    public function directDebitSecuredShouldProhibitAuthorization(SepaDirectDebitSecured $directDebitSecured): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(1.0, 'EUR', $directDebitSecured, self::RETURN_URL);
    }

    /**
     * Verify direct debit secured can be charged.
     *
     * @test
     */
    public function directDebitSecuredShouldAllowCharge(): void
    {
        $directDebitSecured = (new SepaDirectDebitSecured('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->unzer->createPaymentType($directDebitSecured);

        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $basket = $this->createBasket();
        $charge   = $directDebitSecured->charge(100.0, 'EUR', self::RETURN_URL, $customer, null, null, $basket);
        $this->assertTransactionResourceHasBeenCreated($charge);
    }

    /**
     * Verify ddg will throw error if addresses do not match.
     *
     * @test
     */
    public function ddgShouldThrowErrorIfAddressesDoNotMatch(): void
    {
        $directDebitSecured = (new SepaDirectDebitSecured('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->unzer->createPaymentType($directDebitSecured);

        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $customer = $this->getMaximumCustomerInclShippingAddress();
        $basket = $this->createBasket();
        $directDebitSecured->charge(100.0, 'EUR', self::RETURN_URL, $customer, null, null, $basket);
    }
}
