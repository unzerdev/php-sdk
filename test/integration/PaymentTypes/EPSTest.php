<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method EPS.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\EPS;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class EPSTest extends BasePaymentTest
{
    const TEST_BIC = 'STZZATWWXXX';

    /**
     * Verify EPS payment type is creatable.
     *
     * @test
     *
     * @return EPS
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function epsShouldBeCreatable(): EPS
    {
        // Without BIC
        /** @var EPS $eps */
        $eps = $this->heidelpay->createPaymentType(new EPS());
        $this->assertInstanceOf(EPS::class, $eps);
        $this->assertNotNull($eps->getId());

        // With BIC
        /** @var EPS $eps */
        $eps = $this->heidelpay->createPaymentType((new EPS())->setBic(self::TEST_BIC));
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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends epsShouldBeCreatable
     */
    public function epsShouldThrowExceptionOnAuthorize(EPS $eps)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $eps, self::RETURN_URL);
    }

    /**
     * Verify that eps payment type is chargeable.
     *
     * @test
     * @depends epsShouldBeCreatable
     *
     * @param EPS $eps
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function epsShouldBeChargeable(EPS $eps)
    {
        $charge = $eps->charge(1.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $this->assertTrue($charge->getPayment()->isPending());

        $fetchCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchCharge->expose());
    }

    /**
     * Verify eps payment type can be fetched.
     *
     * @test
     * @depends epsShouldBeCreatable
     *
     * @param EPS $eps
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function epsTypeCanBeFetched(EPS $eps)
    {
        $fetchedEPS = $this->heidelpay->fetchPaymentType($eps->getId());
        $this->assertInstanceOf(EPS::class, $fetchedEPS);
        $this->assertEquals($eps->expose(), $fetchedEPS->expose());
    }
}
