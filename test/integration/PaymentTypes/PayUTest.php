<?php

/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method PayUTest.
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
 * @link     https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\integration\PaymentTypes
 */

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */

namespace PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\PayU;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class PayUTest extends BaseIntegrationTest
{
    /**
     * Verify PayU can be created.
     *
     * @test
     *
     * @return PayU
     */
    public function payUShouldBeCreatableAndFetchable(): PayU
    {
        $paymentType = $this->unzer->createPaymentType(new PayU());
        $this->assertInstanceOf(PayU::class, $paymentType);
        $this->assertNotNull($paymentType->getId());

        /** @var PayU $fetchedType */
        $fetchedType = $this->unzer->fetchPaymentType($paymentType->getId());
        $this->assertInstanceOf(PayU::class, $fetchedType);
        $this->assertEquals($paymentType->expose(), $fetchedType->expose());
        $this->assertNotEmpty($fetchedType->getGeoLocation()->getClientIp());

        return $fetchedType;
    }

    /**
     * Verify PayU is chargeable.
     *
     * @test
     *
     * @param PayU  $paymentType
     * @param mixed $currency
     *
     * @return Charge
     *
     * @dataProvider supportedCurrencies
     *
     * @depends      payUShouldBeCreatableAndFetchable
     */
    public function payUShouldBeAbleToCharge($currency, PayU $paymentType): Charge
    {
        $charge = new Charge(100.0, $currency, self::RETURN_URL);
        $this->getUnzerObject()->performCharge($charge, $paymentType);

        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify payU is not authorizable.
     *
     * @test
     *
     * @param PayU $payU
     *
     * @depends payUShouldBeCreatableAndFetchable
     */
    public function payUShouldNotBeAuthorizable(PayU $payU): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $authorization = new Authorization(100.0, 'CHF', self::RETURN_URL);
        $this->unzer->performAuthorization($authorization, $payU);
    }

    public function supportedCurrencies(): array
    {
        return [
            'PLN' => ['PLN'],
            'CZK' => ['CZK']
        ];
    }
}
