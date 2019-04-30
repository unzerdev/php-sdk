<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the payment method Przelewy24.
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
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Przelewy24;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class Przelewy24Test extends BasePaymentTest
{
    /**
     * Verify Przelewy24 payment type can be created and fetched.
     *
     * @test
     *
     * @return BasePaymentType
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function przelewy24ShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $przelewy24 = $this->heidelpay->createPaymentType(new Przelewy24());
        $this->assertInstanceOf(Przelewy24::class, $przelewy24);
        $this->assertNotEmpty($przelewy24->getId());

        $fetchedPrzelewy24 = $this->heidelpay->fetchPaymentType($przelewy24->getId());
        $this->assertInstanceOf(Przelewy24::class, $fetchedPrzelewy24);
        $this->assertNotSame($przelewy24, $fetchedPrzelewy24);
        $this->assertEquals($przelewy24->expose(), $fetchedPrzelewy24->expose());

        return $fetchedPrzelewy24;
    }

    /**
     * Verify przelewy24 can authorize.
     *
     * @test
     * @depends przelewy24ShouldBeCreatableAndFetchable
     *
     * @param Przelewy24 $przelewy24
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function przelewy24ShouldBeChargeable(Przelewy24 $przelewy24)
    {
        $charge = $przelewy24->charge(100.0, 'PLN', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $payment = $charge->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());
    }

    /**
     * Verify przelewy24 can not be authorized.
     *
     * @test
     * @depends przelewy24ShouldBeCreatableAndFetchable
     *
     * @param Przelewy24 $przelewy24
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function przelewy24ShouldNotBeAuthorizable(Przelewy24 $przelewy24)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'PLN', $przelewy24, self::RETURN_URL);
    }

    /**
     * Verify przelewy24 can only handle Currency::POLISH_ZLOTY.
     *
     * @test
     *
     * @dataProvider przelewy24CurrencyCodeProvider
     *
     * @param string $currencyCode
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function przelewy24ShouldThrowExceptionIfCurrencyIsNotSupported($currencyCode)
    {
        /** @var Przelewy24 $przelewy24 */
        $przelewy24 = $this->heidelpay->createPaymentType(new Przelewy24());
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CURRENCY_IS_NOT_SUPPORTED);
        $przelewy24->charge(100.0, $currencyCode, self::RETURN_URL);
    }

    //<editor-fold desc="Data Providers">

    /**
     * Provides a subset of currencies not allowed by this payment method.
     */
    public function przelewy24CurrencyCodeProvider(): array
    {
        $currencyArray = [
            'EUR' => ['EUR'],
            'US Dollar'=> ['USD'],
            'Swiss Franc' => ['CHF']
        ];

        return $currencyArray;
    }

    //</editor-fold>
}
