<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method klarna.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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

use UnzerSDK\Resources\PaymentTypes\Klarna;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class KlarnaTest extends BaseIntegrationTest
{
    /**
     * Verify klarna can be created.
     *
     * @test
     *
     * @return Klarna
     */
    public function klarnaShouldBeCreatableAndFetchable(): Klarna
    {
        $klarna = $this->unzer->createPaymentType(new Klarna());
        $this->assertInstanceOf(Klarna::class, $klarna);
        $this->assertNotNull($klarna->getId());

        /** @var Klarna $fetchedKlarna */
        $fetchedKlarna = $this->unzer->fetchPaymentType($klarna->getId());
        $this->assertInstanceOf(Klarna::class, $fetchedKlarna);
        $this->assertEquals($klarna->expose(), $fetchedKlarna->expose());
        $this->assertNotEmpty($fetchedKlarna->getGeoLocation()->getClientIp());

        return $fetchedKlarna;
    }

    /**
     * Verify klarna is chargeable.
     *
     * @test
     *
     * @param Klarna $klarna
     *
     * @return Charge
     * @depends klarnaShouldBeCreatableAndFetchable
     */
    public function klarnaShouldBeAbleToCharge(Klarna $klarna): Charge
    {
        $chargeInstance = (new Charge(99.99, 'EUR', self::RETURN_URL))
            ->addAdditionalTransactionData('termsAndConditionsUrl', 'https://merchant-terms-url.com')
            ->addAdditionalTransactionData('privacyPolicyUrl', 'https://merchant-data-privacy-url.com');

        $basket = $this->createV2Basket();
        $charge = $this->unzer->performCharge($chargeInstance, $klarna, null, null, $basket);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify klarna is not authorizable.
     *
     * @test
     *
     * @param Klarna $klarna
     * @depends klarnaShouldBeCreatableAndFetchable
     */
    public function klarnaShouldBeAuthorizable(Klarna $klarna): void
    {
        $authorizationInstance = (new Authorization(100.0, 'EUR', self::RETURN_URL))
            ->addAdditionalTransactionData('termsAndConditionsUrl', 'https://merchant-terms-url.com')
            ->addAdditionalTransactionData('privacyPolicyUrl', 'https://merchant-data-privacy-url.com');

        $basket = $this->createV2Basket();
        $authorization = $this->unzer->performAuthorization($authorizationInstance, $klarna, null, null, $basket);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertNotEmpty($authorization->getRedirectUrl());
    }
}
