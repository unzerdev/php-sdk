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
use PHPUnit\Framework\TestCase;

class BasePaymentTest extends TestCase
{
    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const PRIVATE_KEY = 's-priv-6S59Dt6Q9mJYj8X5qpcxSpA3XLXUw4Zf';
    const PUBLIC_KEY = 's-pub-uM8yNmBNcs1GGdwAL4ytebYA4HErD22H';

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::PRIVATE_KEY, SupportedLocale::GERMAN_GERMAN);
    }

    //<editor-fold desc="Helpers">
    /**
     * @return Card
     */
    protected function createCard(): Card
    {
        /** @var Card $card */
        $card = new Card ('4012888888881881', '03/20');
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
    //</editor-fold>

}
