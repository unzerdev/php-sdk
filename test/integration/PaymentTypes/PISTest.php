<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method PIS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/tests/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\PIS;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class PISTest extends BasePaymentTest
{
    /**
     * Verify pis can be created.
     *
     * @test
     *
     * @return PIS
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function pisShouldBeCreatableAndFetchable(): PIS
    {
        $pis = $this->heidelpay->createPaymentType(new PIS());
        $this->assertInstanceOf(PIS::class, $pis);
        $this->assertNotNull($pis->getId());

        /** @var PIS $fetchedPIS */
        $fetchedPIS = $this->heidelpay->fetchPaymentType($pis->getId());
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
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
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
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends pisShouldBeCreatableAndFetchable
     */
    public function pisShouldNotBeAuthorizable(PIS $pis)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, 'EUR', $pis, self::RETURN_URL);
    }
}
