<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Bancontact.
 *
 * Copyright (C) 2020 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  David Owusu <development@heidelpay.com>
 * @author  Florian Evertz <development@heidelpay.com>
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Bancontact;
use heidelpayPHP\test\BaseIntegrationTest;

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
        $this->heidelpay->createPaymentType($bancontact);
        $this->assertNotNull($bancontact->getId());

        $this->heidelpay->fetchPaymentType($bancontact->getId());
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
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $bancontact = $this->heidelpay->createPaymentType(new Bancontact());
        $this->heidelpay->authorize(100.0, 'EUR', $bancontact, self::RETURN_URL);
    }
    
    /**
     * Verify that Bancontact is chargeable
     *
     * @test
     */
    public function bancontactShouldBeChargeable(): void
    {
        /** @var Bancontact $bancontact */
        $bancontact = $this->heidelpay->createPaymentType(new Bancontact());
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
        $bancontact = $this->heidelpay->createPaymentType($bancontact);
        /** @var Bancontact $fetchedBancontact */
        $fetchedBancontact = $this->heidelpay->fetchPaymentType($bancontact->getId());

        $this->assertEquals('test', $fetchedBancontact->getHolder());
    }
}
