<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method GiroPay.
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
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class GiropayTest extends BasePaymentTest
{
    /**
     * Verify a GiroPay resource can be created.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function giroPayShouldBeCreatable()
    {
        /** @var Giropay $giropay */
        $giropay = new Giropay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $this->assertInstanceOf(Giropay::class, $giropay);
        $this->assertNotNull($giropay->getId());
    }

    /**
     * Verify that an exception is thrown when giropay authorize is called.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function giroPayShouldThrowExceptionOnAuthorize()
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $giropay = $this->heidelpay->createPaymentType(new Giropay());
        $this->heidelpay->authorize(1.0, Currencies::EURO, $giropay, self::RETURN_URL);
    }

    /**
     * Verify that GiroPay is chargeable.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     *
     * @group skip
     */
    public function giroPayShouldBeChargeable()
    {
        /** @var Giropay $giropay */
        $giropay = $this->heidelpay->createPaymentType(new Giropay());
        $charge = $giropay->charge(1.0, Currencies::EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());

        $fetchCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchCharge->expose());
    }

    /**
     * Verify a GiroPay object can be fetched from the api.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function giroPayCanBeFetched()
    {
        $giropay = $this->heidelpay->createPaymentType(new Giropay());
        $fetchedGiropay = $this->heidelpay->fetchPaymentType($giropay->getId());
        $this->assertInstanceOf(Giropay::class, $fetchedGiropay);
        $this->assertEquals($giropay->getId(), $fetchedGiropay->getId());
    }
}
