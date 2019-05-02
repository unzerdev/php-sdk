<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method Wechatpay.
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
use heidelpayPHP\Resources\PaymentTypes\Wechatpay;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class WechatpayTest extends BasePaymentTest
{
    /**
     * Verify wechatpay can be created.
     *
     * @test
     *
     * @return Wechatpay
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function wechatpayShouldBeCreatableAndFetchable(): Wechatpay
    {
        $wechatpay = $this->heidelpay->createPaymentType(new Wechatpay());
        $this->assertInstanceOf(Wechatpay::class, $wechatpay);
        $this->assertNotNull($wechatpay->getId());

        /** @var Wechatpay $fetchedWechatpay */
        $fetchedWechatpay = $this->heidelpay->fetchPaymentType($wechatpay->getId());
        $this->assertInstanceOf(Wechatpay::class, $fetchedWechatpay);
        $this->assertEquals($wechatpay->expose(), $fetchedWechatpay->expose());

        return $fetchedWechatpay;
    }

    /**
     * Verify wechatpay is chargeable.
     *
     * @test
     *
     * @param Wechatpay $wechatpay
     *
     * @return Charge
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends wechatpayShouldBeCreatableAndFetchable
     */
    public function wechatpayShouldBeAbleToCharge(Wechatpay $wechatpay): Charge
    {
        $charge = $wechatpay->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify wechatpay is not authorizable.
     *
     * @test
     *
     * @param Wechatpay $wechatpay
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends wechatpayShouldBeCreatableAndFetchable
     */
    public function wechatpayShouldNotBeAuthorizable(Wechatpay $wechatpay)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'EUR', $wechatpay, self::RETURN_URL);
    }
}
