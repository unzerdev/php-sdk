<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sofort.
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
use UnzerSDK\Resources\PaymentTypes\Sofort;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class SofortTest extends BaseIntegrationTest
{
    /**
     * Verify sofort can be created.
     *
     * @test
     *
     * @return Sofort
     */
    public function sofortShouldBeCreatableAndFetchable(): Sofort
    {
        $sofort = $this->unzer->createPaymentType(new Sofort());
        $this->assertInstanceOf(Sofort::class, $sofort);
        $this->assertNotNull($sofort->getId());

        /** @var Sofort $fetchedSofort */
        $fetchedSofort = $this->unzer->fetchPaymentType($sofort->getId());
        $this->assertInstanceOf(Sofort::class, $fetchedSofort);
        $this->assertEquals($sofort->expose(), $fetchedSofort->expose());
        $this->assertNotEmpty($fetchedSofort->getGeoLocation()->getClientIp());

        return $fetchedSofort;
    }

    /**
     * Verify sofort is chargeable.
     *
     * @test
     *
     * @param Sofort $sofort
     *
     * @return Charge
     *
     * @depends sofortShouldBeCreatableAndFetchable
     */
    public function sofortShouldBeAbleToCharge(Sofort $sofort): Charge
    {
        $charge = $sofort->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify sofort is not authorizable.
     *
     * @test
     *
     * @param Sofort $sofort
     *
     * @depends sofortShouldBeCreatableAndFetchable
     */
    public function sofortShouldNotBeAuthorizable(Sofort $sofort): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(100.0, 'EUR', $sofort, self::RETURN_URL);
    }
}
