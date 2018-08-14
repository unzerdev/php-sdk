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
namespace heidelpay\NmgPhpSdk\test\PaymentTypes;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\AbstractPaymentTest;

class CardTest extends AbstractPaymentTest
{
    /**
     * @test
     */
    public function createCardType()
    {
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $this->assertEmpty($card->getId());
        $card = $this->heidelpay->createPaymentType($card);
        /** @var HeidelpayResourceInterface $card */
        $this->assertNotEmpty($card->getId());
    }

    /**
     * @test
     */
    public function authorizeCardType()
    {
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO);
        $this->assertNotNull($authorization);

//        $payment = $authorization->getPayment();
//        $this->assertInstanceOf(Payment::class, $payment);
    }

    /**
     * @test
     */
    public function chargeCardType()
    {
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $card = $this->heidelpay->createPaymentType($card);
        $charge = $card->charge(1.0, Currency::EUROPEAN_EURO);
//        $payment = $charge->getPayment();
        $this->assertNotNull($charge);
	}

	/**
	 * @test
	 */
	public function cancelCardTypeNotAllowed()
	{
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $card = $this->heidelpay->createPaymentType($card);
        $this->expectException(IllegalTransactionTypeException::class);
        $this->expectExceptionMessage('Transaction type cancel is not allowed!');
        $card->cancel();
	}

    /**
     * @test
     */
    public function createdCardTypeHasHeidelpayObject()
    {
        /** @var Card $card */
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        $this->assertEmpty($card->getId());
        $card = $this->heidelpay->createPaymentType($card);
        /** @var HeidelpayParentInterface $card */
        $this->assertSame($this->heidelpay, $card->getHeidelpayObject());
        $this->assertSame($card, $this->heidelpay->getPaymentType());
        $this->assertNotEmpty($card->getId());
    }
}
