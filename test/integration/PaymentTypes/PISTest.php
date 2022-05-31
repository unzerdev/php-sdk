<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method PIS.
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
 * @package  UnzerSDK\tests/integration/payment_types
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\PIS;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class PISTest extends BaseIntegrationTest
{
    /**
     * Verify pis can be created.
     *
     * @test
     *
     * @return PIS
     */
    public function pisShouldBeCreatableAndFetchable(): PIS
    {
        $pis = $this->unzer->createPaymentType(new PIS());
        $this->assertInstanceOf(PIS::class, $pis);
        $this->assertNotNull($pis->getId());

        /** @var PIS $fetchedPIS */
        $fetchedPIS = $this->unzer->fetchPaymentType($pis->getId());
        $this->assertInstanceOf(PIS::class, $fetchedPIS);
        $this->assertEquals($pis->expose(), $fetchedPIS->expose());

        return $fetchedPIS;
    }

    /**
     * Verify pis is chargeable.
     *
     * @test
     *
     * @param PIS $pis
     *
     * @return Charge
     * @depends pisShouldBeCreatableAndFetchable
     */
    public function pisShouldBeAbleToCharge(PIS $pis): Charge
    {
        $charge = $pis->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify pis is not authorizable.
     *
     * @test
     *
     * @param PIS $pis
     * @depends pisShouldBeCreatableAndFetchable
     */
    public function pisShouldNotBeAuthorizable(PIS $pis): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(100.0, 'EUR', $pis, self::RETURN_URL);
    }
}
