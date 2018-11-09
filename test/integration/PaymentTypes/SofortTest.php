<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sepa direct debit.
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
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class SofortTest extends BasePaymentTest
{
    /**
     * Verify sofort can be created.
     *
     * @test
     *
     * @return Sofort
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function sofortShouldBeCreatableAndFetchable(): Sofort
    {
        $sofort = $this->heidelpay->createPaymentType(new Sofort());
        $this->assertInstanceOf(Sofort::class, $sofort);
        $this->assertNotNull($sofort->getId());

        /** @var Sofort $fetchedSofort */
        $fetchedSofort = $this->heidelpay->fetchPaymentType($sofort->getId());
        $this->assertInstanceOf(Sofort::class, $fetchedSofort);
        $this->assertEquals($sofort->expose(), $fetchedSofort->expose());

        return $fetchedSofort;
    }

    /**
     * Verify sofort is chargeable.
     *
     * @test
     *
     * @param Sofort $sofort
     *
     * @return Charge
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @depends sofortShouldBeCreatableAndFetchable
     */
    public function sofortShouldBeAbleToCharge(Sofort $sofort): Charge
    {
        $charge = $sofort->charge(100.0, Currencies::EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());

        return $charge;
    }

    /**
     * Verify sofort is not authorizable.
     *
     * @test
     *
     * @param Sofort $sofort
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @depends sofortShouldBeCreatableAndFetchable
     */
    public function sofortShouldNotBeAuthorizable(Sofort $sofort)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, Currencies::EURO, $sofort, self::RETURN_URL);
    }
}
