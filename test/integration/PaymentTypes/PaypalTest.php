<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method paypal.
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

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class PaypalTest extends BasePaymentTest
{
    /**
     * Verify PayPal payment type can be created and fetched.
     *
     * @test
     *
     * @return BasePaymentType
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function paypalShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $this->assertInstanceOf(Paypal::class, $paypal);
        $this->assertNotEmpty($paypal->getId());

        $fetchedPaypal = $this->heidelpay->fetchPaymentType($paypal->getId());
        $this->assertInstanceOf(Paypal::class, $fetchedPaypal);
        $this->assertNotSame($paypal, $fetchedPaypal);
        $this->assertEquals($paypal->expose(), $fetchedPaypal->expose());

        return $fetchedPaypal;
    }

    /**
     * Verify PayPal payment type can be created and fetched with email.
     *
     * @test
     *
     * @return BasePaymentType
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function paypalShouldBeCreatableAndFetchableWithEmail(): BasePaymentType
    {
        $paypal = (new Paypal())->setEmail('max@mustermann.de');
        $this->heidelpay->createPaymentType($paypal);
        $this->assertNotEmpty($paypal->getId());

        $fetchedPaypal = $this->heidelpay->fetchPaymentType($paypal->getId());
        $this->assertInstanceOf(Paypal::class, $fetchedPaypal);
        $this->assertNotSame($paypal, $fetchedPaypal);
        $this->assertEquals($paypal->expose(), $fetchedPaypal->expose());

        return $fetchedPaypal;
    }

    /**
     * Verify paypal can authorize.
     *
     * @test
     * @depends paypalShouldBeCreatableAndFetchable
     *
     * @param Paypal $paypal
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function paypalShouldBeAuthorizable(Paypal $paypal)
    {
        $authorization = $paypal->authorize(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertNotEmpty($authorization->getRedirectUrl());

        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());
    }

    /**
     * Verify paypal can charge.
     *
     * @test
     * @depends paypalShouldBeCreatableAndFetchable
     *
     * @param Paypal $paypal
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function paypalShouldBeChargeable(Paypal $paypal)
    {
        $charge = $paypal->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
    }
}
