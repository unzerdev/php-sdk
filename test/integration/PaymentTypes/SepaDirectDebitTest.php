<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sepa direct debit.
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
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class SepaDirectDebitTest extends BasePaymentTest
{
    /**
     * Verify sepa direct debit can be created.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function sepaDirectDebitShouldBeCreatableWithMandatoryFieldsOnly()
    {
        $directDebit = new SepaDirectDebit('DE89370400440532013000');
        /** @var SepaDirectDebit $directDebit */
        $directDebit = $this->heidelpay->createPaymentType($directDebit);
        $this->assertInstanceOf(SepaDirectDebit::class, $directDebit);
        $this->assertNotNull($directDebit->getId());

        /** @var SepaDirectDebit $fetchedDirectDebit */
        $fetchedDirectDebit = $this->heidelpay->fetchPaymentType($directDebit->getId());
        $this->assertEquals($directDebit->expose(), $fetchedDirectDebit->expose());
    }

    /**
     * Verify sepa direct debit can be created.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function sepaDirectDebitShouldBeCreatable()
    {
        $sdd = (new SepaDirectDebit('DE89370400440532013000'))->setHolder('Max Mustermann')->setBic('COBADEFFXXX');
        /** @var SepaDirectDebit $sdd */
        $sdd = $this->heidelpay->createPaymentType($sdd);
        $this->assertInstanceOf(SepaDirectDebit::class, $sdd);
        $this->assertNotNull($sdd->getId());

        /** @var SepaDirectDebit $fetchedDirectDebit */
        $fetchedDirectDebit = $this->heidelpay->fetchPaymentType($sdd->getId());
        $this->assertEquals($sdd->expose(), $fetchedDirectDebit->expose());
    }

    /**
     * Verify authorization is not allowed for sepa direct debit.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function authorizeShouldThrowException()
    {
        /** @var SepaDirectDebit $sdd */
        $sdd = (new SepaDirectDebit('DE89370400440532013000'))->setHolder('Max Mustermann')->setBic('COBADEFFXXX');
        $sdd = $this->heidelpay->createPaymentType($sdd);
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $sdd, self::RETURN_URL);
    }

    /**
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function directDebitShouldBeChargeable()
    {
        /** @var SepaDirectDebit $sdd */
        $sdd = (new SepaDirectDebit('DE89370400440532013000'))->setHolder('Max Mustermann')->setBic('COBADEFFXXX');
        $sdd = $this->heidelpay->createPaymentType($sdd);
        $charge = $sdd->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
    }

    /**
     * Verify sdd charge is refundable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function directDebitChargeShouldBeRefundable()
    {
        /** @var SepaDirectDebit $sdd */
        $sdd = (new SepaDirectDebit('DE89370400440532013000'))->setHolder('Max Mustermann')->setBic('COBADEFFXXX');
        $sdd = $this->heidelpay->createPaymentType($sdd);
        $charge = $sdd->charge(100.0, 'EUR', self::RETURN_URL);

        // when
        $cancellation = $charge->cancel();

        // then
        $this->assertNotNull($cancellation);
        $this->assertNotNull($cancellation->getId());
    }
}
