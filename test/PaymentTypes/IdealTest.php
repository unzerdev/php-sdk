<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the payment method Ideal.
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
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class IdealTest extends BasePaymentTest
{
    /**
     * Verify Ideal payment type is creatable.
     *
     * @test
     * @return Ideal
     * todo: change bankname to bic
     */
    public function idealShouldBeCreatable(): Ideal
    {
        /** @var Ideal $ideal */
        $ideal = new Ideal();
        $ideal->setBankName('RABONL2U');
        $this->heidelpay->createPaymentType($ideal);
        $this->assertInstanceOf(Ideal::class, $ideal);
        $this->assertNotNull($ideal->getId());

        return $ideal;
    }

    /**
     * Verify that ideal is not authorizable
     *
     * @test
     * // todo fix when ideal operation is correctly defined.
     * @param Ideal $ideal
     * @depends idealShouldBeCreatable
     */
    public function idealShouldThrowExceptionOnAuthorize(Ideal $ideal)
    {
        $this->expectException(IllegalTransactionTypeException::class);
        $ideal->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * Verify that ideal payment type is chargeable.
     *
     * @test
     * @depends idealShouldBeCreatable
     * @param Ideal $ideal
     */
    public function idealShouldBeChargeable(Ideal $ideal)
    {
		$charge = $ideal->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
		$this->assertNotNull($charge);
		$this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());
    }

    /**
     * Verify ideal payment type can be fetched.
     *
     * @test
     * @depends idealShouldBeCreatable
     * @param Ideal $ideal
     */
    public function idealTypeCanBeFetched(Ideal $ideal)
    {
		$fetchedIdeal = $this->heidelpay->fetchPaymentType($ideal->getId());
        $this->assertInstanceOf(Ideal::class, $fetchedIdeal);
        $this->assertEquals($ideal->getId(), $fetchedIdeal->getId());
    }
}
