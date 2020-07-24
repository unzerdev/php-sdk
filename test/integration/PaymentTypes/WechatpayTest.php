<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Wechatpay;
use heidelpayPHP\test\BaseIntegrationTest;

class WechatpayTest extends BaseIntegrationTest
{
    /**
     * Verify wechatpay can be created.
     *
     * @test
     */
    public function wechatpayShouldBeCreatableAndFetchable(): void
    {
        $wechatpay = $this->heidelpay->createPaymentType(new Wechatpay());
        $this->assertInstanceOf(Wechatpay::class, $wechatpay);
        $this->assertNotNull($wechatpay->getId());

        /** @var Wechatpay $fetchedWechatpay */
        $fetchedWechatpay = $this->heidelpay->fetchPaymentType($wechatpay->getId());
        $this->assertInstanceOf(Wechatpay::class, $fetchedWechatpay);
        $this->assertEquals($wechatpay->expose(), $fetchedWechatpay->expose());
    }

    /**
     * Verify wechatpay is chargeable.
     *
     * @test
     */
    public function wechatpayShouldBeAbleToCharge(): void
    {
        /** @var Wechatpay $wechatpay */
        $wechatpay = $this->heidelpay->createPaymentType(new Wechatpay());
        $charge = $wechatpay->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());
    }

    /**
     * Verify wechatpay is not authorizable.
     *
     * @test
     */
    public function wechatpayShouldNotBeAuthorizable(): void
    {
        $wechatpay = $this->heidelpay->createPaymentType(new Wechatpay());
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'EUR', $wechatpay, self::RETURN_URL);
    }
}
