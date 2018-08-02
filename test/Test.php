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

use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    const KEY = '123456789';
    const RETURN_URL = 'returnURL.php';

    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::KEY, self::RETURN_URL);
    }

    /**
     * @test
     */
    public function heidelpayObjectShouldHaveGettersAndSettersForProperties()
    {
        $this->assertSame(self::KEY, $this->heidelpay->getKey());
        $this->assertTrue($this->heidelpay->isSandboxMode());
        $this->assertSame(self::RETURN_URL, $this->heidelpay->getReturnUrl());

        $returnUrl = 'newReturnURL.php';
        $key = '987654321';
        $sandboxMode = false;

        $this->heidelpay->setSandboxMode($sandboxMode);
        $this->heidelpay->setKey($key);
        $this->heidelpay->setReturnUrl($returnUrl);

        $this->assertSame($key, $this->heidelpay->getKey());
        $this->assertEquals($sandboxMode, $this->heidelpay->isSandboxMode());
        $this->assertSame($returnUrl, $this->heidelpay->getReturnUrl());
    }

    /**
     * @test
     */
    public function heidelpayObjectShouldCreatePaymentObject()
    {
        $card = new Card('123456789', '09', '2019', '123');
        $card->setHolder('Max Mustermann');

        /** @var Payment $payment */
        $payment = $this->heidelpay->createPayment($card);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($card, $payment->getPaymentType());
    }
}
