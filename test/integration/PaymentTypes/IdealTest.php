<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Ideal.
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
use heidelpayPHP\Resources\PaymentTypes\Ideal;
use heidelpayPHP\test\BaseIntegrationTest;

class IdealTest extends BaseIntegrationTest
{
    /**
     * Verify Ideal payment type is creatable.
     *
     * @test
     *
     * @return Ideal
     */
    public function idealShouldBeCreatable(): Ideal
    {
        /** @var Ideal $ideal */
        $ideal = $this->heidelpay->createPaymentType((new Ideal())->setBic('RABONL2U'));
        $this->assertInstanceOf(Ideal::class, $ideal);
        $this->assertNotNull($ideal->getId());

        return $ideal;
    }

    /**
     * Verify that ideal is not authorizable.
     *
     * @test
     *
     * @param Ideal $ideal
     * @depends idealShouldBeCreatable
     */
    public function idealShouldThrowExceptionOnAuthorize(Ideal $ideal): void
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $ideal, self::RETURN_URL);
    }

    /**
     * Verify that ideal payment type is chargeable.
     *
     * @test
     * @depends idealShouldBeCreatable
     *
     * @param Ideal $ideal
     */
    public function idealShouldBeChargeable(Ideal $ideal): void
    {
        $charge = $ideal->charge(1.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $fetchCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchCharge->expose());
    }

    /**
     * Verify ideal payment type can be fetched.
     *
     * @test
     * @depends idealShouldBeCreatable
     *
     * @param Ideal $ideal
     */
    public function idealTypeCanBeFetched(Ideal $ideal): void
    {
        $fetchedIdeal = $this->heidelpay->fetchPaymentType($ideal->getId());
        $this->assertInstanceOf(Ideal::class, $fetchedIdeal);
        $this->assertEquals($ideal->getId(), $fetchedIdeal->getId());
    }
}
