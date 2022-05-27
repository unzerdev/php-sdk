<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Bancontact.
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
 @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Bancontact;
use UnzerSDK\test\BaseIntegrationTest;

class BancontactTest extends BaseIntegrationTest
{
    /**
     * Verify bancontact can be created and fetched.
     *
     * @test
     */
    public function bancontactShouldBeCreatableAndFetchable(): void
    {
        $bancontact = new Bancontact();
        $this->unzer->createPaymentType($bancontact);
        $this->assertNotNull($bancontact->getId());

        $this->unzer->fetchPaymentType($bancontact->getId());
        $this->assertInstanceOf(Bancontact::class, $bancontact);
        $this->assertNull($bancontact->getHolder());
    }

    /**
     * Verify that an exception is thrown when bancontact authorize is called.
     *
     * @test
     */
    public function bancontactShouldThrowExceptionOnAuthorize(): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $bancontact = $this->unzer->createPaymentType(new Bancontact());
        $this->unzer->authorize(100.0, 'EUR', $bancontact, self::RETURN_URL);
    }
    
    /**
     * Verify that Bancontact is chargeable
     *
     * @test
     */
    public function bancontactShouldBeChargeable(): void
    {
        /** @var Bancontact $bancontact */
        $bancontact = $this->unzer->createPaymentType(new Bancontact());
        $charge = $bancontact->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());
    }

    /**
     * Holder parameter is correctly submitted.
     *
     * @test
     */
    public function holderShouldBeSubmittedCorrectly(): void
    {
        $bancontact = new Bancontact();
        $bancontact->setHolder('test');
        $bancontact = $this->unzer->createPaymentType($bancontact);
        /** @var Bancontact $fetchedBancontact */
        $fetchedBancontact = $this->unzer->fetchPaymentType($bancontact->getId());

        $this->assertEquals('test', $fetchedBancontact->getHolder());
    }
}
