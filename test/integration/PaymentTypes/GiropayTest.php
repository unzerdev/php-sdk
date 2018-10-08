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
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\GiroPay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

class GiropayTest extends BasePaymentTest
{
    /**
     * Verify a GiroPay resource can be created.
     *
     * @test
     */
    public function giroPayShouldBeCreatable()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $this->assertInstanceOf(GiroPay::class, $giropay);
        $this->assertNotNull($giropay->getId());
    }

    /**
     * Verify that an exception is thrown when giropay authorize is called.
     *
     * @test
     */
    public function giroPayShouldThrowExceptionOnAuthorize()
    {
        $this->expectException(IllegalTransactionTypeException::class);

        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $giropay->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * Verify that GiroPay is chargeable.
     *
     * @test
     */
    public function giroPayShouldBeChargeable()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);

        /** @var Charge $charge */
        $charge = $giropay->charge(1.0, currency::EUROPEAN_EURO, self::RETURN_URL);

        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());
    }

    /**
     * Verify a GiroPay object can be fetched from the api.
     *
     * @test
     */
    public function giroPayCanBeFetched()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);

        $fetchedGiropay = $this->heidelpay->fetchPaymentType($giropay->getId());
        $this->assertInstanceOf(GiroPay::class, $fetchedGiropay);
        $this->assertEquals($giropay->getId(), $fetchedGiropay->getId());
    }
}
