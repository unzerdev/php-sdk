<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentInterface;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\TestCase;

class BasePaymentTest extends TestCase
{
    use CustomerFixtureTrait;

    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const RETURN_URL = 'http://vnexpress.vn';
    const PRIVATE_KEY_1 = 's-priv-2a108n6lxMV581sTKL87VxiAmuzz6VMH';
    const PRIVATE_KEY_2 = 's-priv-6S59Dt6Q9mJYj8X5qpcxSpA3XLXUw4Zf';
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
        $this->assertEquals($expectedRemaining, $payment->getRemaining());
        $this->assertEquals($expectedCharged, $payment->getCharged());
        $this->assertEquals($expectedTotal, $payment->getTotal());
        $this->assertEquals($expectedCanceled, $payment->getCanceled());
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
