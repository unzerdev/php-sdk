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

}
