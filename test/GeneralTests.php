<?php
/**
 * This class defines integration tests to verify general functionalities of this SDK.
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
 * @package  heidelpay/ngw_sdk/tests/integration
 */
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\NmgPhpSdk\Heidelpay;

class GeneralTests extends BasePaymentTest
{
    /**
     * @test
     */
    public function heidelpayObjectShouldThrowExceptionWhenKeyIsPublic()
    {
        $this->expectException(IllegalKeyException::class);
        $this->heidelpay = new Heidelpay(BasePaymentTest::PUBLIC_KEY);
    }

    /**
     * @test
     */
    public function shouldFetchPayment()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $payment->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment->charge(5.0);
        $this->assertAmounts($payment, 7, 5, 12, 0);

        $secondPayment = $this->createPayment();
        $this->assertEmpty($secondPayment->getId());
        $this->assertAmounts($secondPayment, 0, 0, 0, 0);
        $this->assertTrue($secondPayment->isPending());
        $secondPayment->setId($payment->getId());
        $secondPayment->fetch();
        $this->assertAmounts($secondPayment, 7, 5, 12, 0);
        $this->assertTrue($secondPayment->isPartlyPaid());
    }

    /**
     * @test
     */
    public function paymentCanBeFetchedById()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $authorization = $payment->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertEquals(12.0, $authorization->getAmount());

        $secPayment = $this->heidelpay->fetchPaymentById($payment->getId());
        $this->assertSame($payment->getId(), $secPayment->getId());
    }
}
