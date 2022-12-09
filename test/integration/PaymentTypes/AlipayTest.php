<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method Alipay.
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
use UnzerSDK\Resources\PaymentTypes\Alipay;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class AlipayTest extends BaseIntegrationTest
{
    /**
     * Verify alipay can be created.
     *
     * @test
     *
     * @return Alipay
     */
    public function alipayShouldBeCreatableAndFetchable(): Alipay
    {
        $alipay = $this->unzer->createPaymentType(new Alipay());
        $this->assertInstanceOf(Alipay::class, $alipay);
        $this->assertNotNull($alipay->getId());

        /** @var Alipay $fetchedAlipay */
        $fetchedAlipay = $this->unzer->fetchPaymentType($alipay->getId());
        $this->assertInstanceOf(Alipay::class, $fetchedAlipay);
        $this->assertEquals($alipay->expose(), $fetchedAlipay->expose());

        return $fetchedAlipay;
    }

    /**
     * Verify alipay is chargeable.
     *
     * @test
     *
     * @param Alipay $alipay
     *
     * @return Charge
     *
     * @depends alipayShouldBeCreatableAndFetchable
     */
    public function alipayShouldBeAbleToCharge(Alipay $alipay): Charge
    {
        $charge = $alipay->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify alipay is not authorizable.
     *
     * @test
     *
     * @param Alipay $alipay
     *
     * @depends alipayShouldBeCreatableAndFetchable
     */
    public function alipayShouldNotBeAuthorizable(Alipay $alipay): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(100.0, 'EUR', $alipay, self::RETURN_URL);
    }
}
