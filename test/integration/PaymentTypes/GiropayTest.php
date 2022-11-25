<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method GiroPay.
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
use UnzerSDK\Resources\PaymentTypes\Giropay;
use UnzerSDK\test\BaseIntegrationTest;

class GiropayTest extends BaseIntegrationTest
{
    /**
     * Verify a GiroPay resource can be created.
     *
     * @test
     */
    public function giroPayShouldBeCreatable(): void
    {
        $giropay = new Giropay();
        /** @var Giropay $giropay */
        $giropay = $this->unzer->createPaymentType($giropay);
        $this->assertInstanceOf(Giropay::class, $giropay);
        $this->assertNotNull($giropay->getId());
    }

    /**
     * Verify that an exception is thrown when giropay authorize is called.
     *
     * @test
     */
    public function giroPayShouldThrowExceptionOnAuthorize(): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $giropay = $this->unzer->createPaymentType(new Giropay());
        $this->unzer->authorize(1.0, 'EUR', $giropay, self::RETURN_URL);
    }

    /**
     * Verify that GiroPay is chargeable.
     *
     * @test
     */
    public function giroPayShouldBeChargeable(): void
    {
        /** @var Giropay $giropay */
        $giropay = $this->unzer->createPaymentType(new Giropay());
        $charge = $giropay->charge(1.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $fetchCharge = $this->unzer->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->setCard3ds(false)->expose(), $fetchCharge->expose());
    }

    /**
     * Verify a GiroPay object can be fetched from the api.
     *
     * @test
     */
    public function giroPayCanBeFetched(): void
    {
        $giropay = $this->unzer->createPaymentType(new Giropay());
        $fetchedGiropay = $this->unzer->fetchPaymentType($giropay->getId());
        $this->assertInstanceOf(Giropay::class, $fetchedGiropay);
        $this->assertEquals($giropay->getId(), $fetchedGiropay->getId());
    }
}
