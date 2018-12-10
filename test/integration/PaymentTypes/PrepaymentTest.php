<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method prepayment.
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
 * @package  heidelpay/mgw_sdk/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\PaymentTypes\Prepayment;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class PrepaymentTest extends BasePaymentTest
{
    /**
     * Verify Prepayment can be created and fetched.
     *
     * @return Prepayment
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @test
     */
    public function prepaymentShouldBeCreatableAndFetchable(): AbstractHeidelpayResource
    {
        $prepayment = $this->heidelpay->createPaymentType(new Prepayment());
        $this->assertInstanceOf(Prepayment::class, $prepayment);
        $this->assertNotEmpty($prepayment->getId());

        $fetchedPrepayment = $this->heidelpay->fetchPaymentType($prepayment->getId());
        $this->assertInstanceOf(Prepayment::class, $fetchedPrepayment);
        $this->assertEquals($prepayment->expose(), $fetchedPrepayment->expose());

        return $fetchedPrepayment;
    }

    /**
     * Verify authorization of prepayment type.
     *
     * @test
     *
     * @depends prepaymentShouldBeCreatableAndFetchable
     *
     * @param Prepayment $prepayment
     *
     * @return Authorization
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function prepaymentTypeShouldBeAuthorizable(Prepayment $prepayment): Authorization
    {
        $authorization = $prepayment->authorize(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotNull($authorization->getId());
        $this->assertNotEmpty($authorization->getIban());
        $this->assertNotEmpty($authorization->getBic());
        $this->assertNotEmpty($authorization->getHolder());
        $this->assertNotEmpty($authorization->getDescriptor());

        return $authorization;
    }

    /**
     * Verify charging a prepayment throws an exception.
     *
     * @test
     *
     * @depends prepaymentShouldBeCreatableAndFetchable
     *
     * @param Prepayment $prepayment
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function prepaymentTypeShouldNotBeChargeable(Prepayment $prepayment)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_CHARGE_NOT_ALLOWED);

        $prepayment->charge(100.0, 'EUR', self::RETURN_URL);
    }

    /**
     * Verify shipment on a prepayment throws an exception.
     *
     * @test
     *
     * @depends prepaymentTypeShouldBeAuthorizable
     *
     * @param Authorization $authorization
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function prepaymentTypeShouldNotBeShippable(Authorization $authorization)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_SHIP_NOT_ALLOWED);

        $this->heidelpay->ship($authorization->getPayment());
    }

    /**
     * Verify authorization of prepayment type.
     *
     * @test
     *
     * @depends prepaymentShouldBeCreatableAndFetchable
     *
     * @param Prepayment $prepayment
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function prepaymentAuthorizeCanBeCanceled(Prepayment $prepayment)
    {
        $authorization = $prepayment->authorize(100.0, 'EUR', self::RETURN_URL);
        $cancellation = $authorization->cancel();
        $this->assertNotNull($cancellation);
        $this->assertNotNull($cancellation->getId());
    }
}
