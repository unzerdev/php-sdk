<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method Alipay.
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
use heidelpayPHP\Resources\PaymentTypes\Alipay;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class AlipayTest extends BasePaymentTest
{
    /**
     * Verify alipay can be created.
     *
     * @test
     *
     * @return Alipay
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function alipayShouldBeCreatableAndFetchable(): Alipay
    {
        $alipay = $this->heidelpay->createPaymentType(new Alipay());
        $this->assertInstanceOf(Alipay::class, $alipay);
        $this->assertNotNull($alipay->getId());

        /** @var Alipay $fetchedAlipay */
        $fetchedAlipay = $this->heidelpay->fetchPaymentType($alipay->getId());
        $this->assertInstanceOf(Alipay::class, $fetchedAlipay);
        $this->assertEquals($alipay->expose(), $fetchedAlipay->expose());

        return $fetchedAlipay;
    }

    /**
     * Verify alipay is chargeable.
     *
     * @test
     *
     * @param Alipay $alipay
     *
     * @return Charge
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends alipayShouldBeCreatableAndFetchable
     */
    public function alipayShouldBeAbleToCharge(Alipay $alipay): Charge
    {
        $charge = $alipay->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify alipay is not authorizable.
     *
     * @test
     *
     * @param Alipay $alipay
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends alipayShouldBeCreatableAndFetchable
     */
    public function alipayShouldNotBeAuthorizable(Alipay $alipay)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'EUR', $alipay, self::RETURN_URL);
    }
}
