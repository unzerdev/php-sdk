<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Ideal.
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
 * @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Ideal;
use UnzerSDK\test\BaseIntegrationTest;

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
        $ideal = $this->unzer->createPaymentType((new Ideal())->setBic('RABONL2U'));
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
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(1.0, 'EUR', $ideal, self::RETURN_URL);
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

        $fetchCharge = $this->unzer->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->setCard3ds(false)->expose(), $fetchCharge->expose());
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
        $fetchedIdeal = $this->unzer->fetchPaymentType($ideal->getId());
        $this->assertInstanceOf(Ideal::class, $fetchedIdeal);
        $this->assertEquals($ideal->getId(), $fetchedIdeal->getId());
    }
}
