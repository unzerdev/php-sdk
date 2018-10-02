<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method GiroPay.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\PaymentTypes;

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
