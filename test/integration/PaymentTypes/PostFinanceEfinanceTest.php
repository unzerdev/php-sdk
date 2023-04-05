<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method PostFinanceEfinanceTest.
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

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\PostFinanceEfinance;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class PostFinanceEfinanceTest extends BaseIntegrationTest
{
    /**
     * Verify PostFinanceEfinance can be created.
     *
     * @test
     *
     * @return PostFinanceEfinance
     */
    public function postFinanceEfinanceShouldBeCreatableAndFetchable(): PostFinanceEfinance
    {
        $paymentType = $this->unzer->createPaymentType(new PostFinanceEfinance());
        $this->assertInstanceOf(PostFinanceEfinance::class, $paymentType);
        $this->assertNotNull($paymentType->getId());

        /** @var PostFinanceEfinance $fetchedType */
        $fetchedType = $this->unzer->fetchPaymentType($paymentType->getId());
        $this->assertInstanceOf(PostFinanceEfinance::class, $fetchedType);
        $this->assertEquals($paymentType->expose(), $fetchedType->expose());
        $this->assertNotEmpty($fetchedType->getGeoLocation()->getClientIp());

        return $fetchedType;
    }

    /**
     * Verify PostFinanceEfinance is chargeable.
     *
     * @test
     *
     * @param PostFinanceEfinance $paymentType
     *
     * @return Charge
     *
     * @depends postFinanceEfinanceShouldBeCreatableAndFetchable
     */
    public function postFinanceEfinanceShouldBeAbleToCharge(PostFinanceEfinance $paymentType): Charge
    {
        $charge = new Charge(100.0, 'CHF', self::RETURN_URL);
        $this->getUnzerObject()->performCharge($charge, $paymentType);

        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify PostFinanceEfinance is not authorizable.
     *
     * @test
     *
     * @param PostFinanceEfinance $postFinanceEfinance
     *
     * @depends postFinanceEfinanceShouldBeCreatableAndFetchable
     */
    public function postFinanceEfinanceShouldNotBeAuthorizable(PostFinanceEfinance $postFinanceEfinance): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $authorization = new Authorization(100.0, 'CHF', self::RETURN_URL);
        $this->unzer->performAuthorization($authorization, $postFinanceEfinance);
    }
}
