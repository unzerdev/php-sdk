<?php
/**
 * This class is the base class for all integration tests of this SDK.
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test;

use heidelpay\MgwPhpSdk\Constants\SupportedLocale;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Interfaces\PaymentInterface;
use heidelpay\MgwPhpSdk\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\TestCase;

class BasePaymentTest extends TestCase
{
    use CustomerFixtureTrait;

    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const RETURN_URL = 'http://vnexpress.vn';
    const PRIVATE_KEY_1 = 's-priv-6S59Dt6Q9mJYj8X5qpcxSpA3XLXUw4Zf';
    const PRIVATE_KEY_2 = 's-priv-2a108n6lxMV581sTKL87VxiAmuzz6VMH';
    const PRIVATE_KEY_NOT_PCI_DDS_COMPLIANT = 's-priv-2a107CYZMp3UbyVPAuqWoxQHi9nFyeiW';
    const PUBLIC_KEY = 's-pub-uM8yNmBNcs1GGdwAL4ytebYA4HErD22H';

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::PRIVATE_KEY_1, SupportedLocale::GERMAN_GERMAN);
    }

    //<editor-fold desc="Helpers">
    /**
     * @return Card
     */
    protected function createCard(): Card
    {
        /** @var Card $card */
        $card = new Card ('4444333322221111', '03/20');
        $card->setCvc('123');
        return $card;
    }

    /**
     * @return Payment
     */
    protected function createPayment(): Payment
    {
        // todo: alternative -> add create payment method to heidelpay
        return new Payment($this->heidelpay);
    }

    /**
     * @param PaymentInterface $payment
     * @param float $expectedRemaining
     * @param float $expectedCharged
     * @param float $expectedTotal
     * @param float $expectedCanceled
     */
    protected function assertAmounts(
        $payment,
        $expectedRemaining,
        $expectedCharged,
        $expectedTotal,
        $expectedCanceled
    ) {
        $this->assertEquals($expectedRemaining, $payment->getRemaining(), 'The remaining amount does not match.');
        $this->assertEquals($expectedCharged, $payment->getCharged(), 'The charged amount does not match.');
        $this->assertEquals($expectedTotal, $payment->getTotal(), 'The total amount does not match.');
        $this->assertEquals($expectedCanceled, $payment->getCanceled(), 'The canceled amount does not match.');
    }

    /**
     * Mask a credit card number.
     *
     * @param $number
     * @param string $maskSymbol
     * @return string
     */
    protected function maskCreditCardNumber($number, $maskSymbol = '*'): string
    {
        return substr($number, 0, 6) . str_repeat($maskSymbol, \strlen($number) - 10) . substr($number, -4);
    }
    //</editor-fold>

}
