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
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\AbstractPaymentTest;

class CardTest extends AbstractPaymentTest
{
    /**
     * @test
     */
    public function createCardType()
    {
        $card = $this->createCard();
        $this->assertEmpty($card->getId());
        $card = $this->heidelpay->createPaymentType($card);
        /** @var HeidelpayResourceInterface $card */
        $this->assertNotEmpty($card->getId());

        /** @var HeidelpayParentInterface $card */
        $this->assertSame($this->heidelpay, $card->getHeidelpayObject());
        $this->assertSame($card, $this->heidelpay->getPaymentType());

        return $card;
    }

    /**
     * @param Card $card
     * @depends createCardType
     * @test
     */
    public function authorizeCardType(Card $card)
    {
        $this->assertNull($card->getPayment());
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO, 'http://vnexpress.vn');
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertInstanceOf(Payment::class, $authorization->getPayment());
        $this->assertNotEmpty($authorization->getPayment()->getId());
    }

    /**
     * @param Card $card
     * @depends createCardType
     * @test
     */
    public function chargeCardType(Card $card)
    {
        $charge = $card->charge(1.0, Currency::EUROPEAN_EURO);
//        $payment = $charge->getPayment();
        $this->assertNotNull($charge);
	}

	/**
     * @param Card $card
     * @depends createCardType
	 * @test
	 */
	public function cancelCardTypeNotAllowed(Card $card)
	{
        $this->expectException(IllegalTransactionTypeException::class);
        $this->expectExceptionMessage('Transaction type cancel is not allowed!');
        $card->cancel();
	}

    /**
     * @return Card
     */
    private function createCard(): Card
    {
        /** @var Card $card */
        $card = new Card ('4111111111111111', '03/20');
        $card->setCvc('123');
        return $card;
    }
}
