<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method EPS.
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
use UnzerSDK\Resources\PaymentTypes\EPS;
use UnzerSDK\test\BaseIntegrationTest;

class EPSTest extends BaseIntegrationTest
{
    private const TEST_BIC = 'STZZATWWXXX';

    /**
     * Verify EPS payment type is creatable.
     *
     * @test
     *
     * @return EPS
     */
    public function epsShouldBeCreatable(): EPS
    {
        // Without BIC
        /** @var EPS $eps */
        $eps = $this->unzer->createPaymentType(new EPS());
        $this->assertInstanceOf(EPS::class, $eps);
        $this->assertNotNull($eps->getId());

        // With BIC
        /** @var EPS $eps */
        $eps = $this->unzer->createPaymentType((new EPS())->setBic(self::TEST_BIC));
        $this->assertInstanceOf(EPS::class, $eps);
        $this->assertNotNull($eps->getId());

        return $eps;
    }

    /**
     * Verify that eps is not authorizable.
     *
     * @test
     *
     * @param EPS $eps
     *
     * @depends epsShouldBeCreatable
     */
    public function epsShouldThrowExceptionOnAuthorize(EPS $eps): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(1.0, 'EUR', $eps, self::RETURN_URL);
    }

    /**
     * Verify that eps payment type is chargeable.
     *
     * @test
     *
     * @depends epsShouldBeCreatable
     *
     * @param EPS $eps
     */
    public function epsShouldBeChargeable(EPS $eps): void
    {
        $charge = $eps->charge(1.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $this->assertTrue($charge->getPayment()->isPending());

        $fetchCharge = $this->unzer->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->setCard3ds(false)->expose(), $fetchCharge->expose());
    }

    /**
     * Verify eps payment type can be fetched.
     *
     * @test
     *
     * @depends epsShouldBeCreatable
     *
     * @param EPS $eps
     */
    public function epsTypeCanBeFetched(EPS $eps): void
    {
        $fetchedEPS = $this->unzer->fetchPaymentType($eps->getId());
        $this->assertInstanceOf(EPS::class, $fetchedEPS);
        $this->assertEquals($eps->expose(), $fetchedEPS->expose());
    }
}
