<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method prepayment.
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
use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Prepayment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class PrepaymentTest extends BasePaymentTest
{
    /**
     * Verify Prepayment can be created and fetched.
     *
     * @return Prepayment
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     * @test
     */
    public function prepaymentShouldBeCreatableAndFetchable(): AbstractHeidelpayResource
    {
        $prepayment = $this->heidelpay->createPaymentType(new Prepayment());
        $this->assertInstanceOf(Prepayment::class, $prepayment);
        $this->assertNotEmpty($prepayment->getId());

        $fetchedPrepayment = $this->heidelpay->fetchPaymentType($prepayment->getId());
        $this->assertInstanceOf(Prepayment::class, $fetchedPrepayment);
        $this->assertEquals($prepayment->expose(), $fetchedPrepayment->expose());

        return $fetchedPrepayment;
    }

    /**
     * Verify authorization of prepayment type.
     *
     * @test
     *
     * @depends prepaymentShouldBeCreatableAndFetchable
     *
     * @param BasePaymentType $prepayment
     *
     * @return Authorization
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function prepaymentTypeShouldBeAuthorizable(BasePaymentType $prepayment): Authorization
    {
        $authorization = $prepayment->authorize(100.0, Currency::EURO, self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotNull($authorization->getId());

        return $authorization;
    }

    /**
     * Verify charging a prepayment throws an exception.
     *
     * @test
     *
     * @depends prepaymentShouldBeCreatableAndFetchable
     *
     * @param BasePaymentType $prepayment
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function prepaymentTypeShouldNotBeChargeable(BasePaymentType $prepayment)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_CHARGE_NOT_ALLOWED);

        $prepayment->charge(100.0, Currency::EURO, self::RETURN_URL);
    }

    /**
     * Verify shipment on a prepayment throws an exception.
     *
     * @test
     *
     * @depends prepaymentTypeShouldBeAuthorizable
     *
     * @param Authorization $authorization
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function prepaymentTypeShouldNotBeShippable(Authorization $authorization)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_SHIP_NOT_ALLOWED);

        $this->heidelpay->ship($authorization->getPayment());
    }
}
