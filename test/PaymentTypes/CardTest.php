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
use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\AbstractPaymentTest;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;

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
     * @return Authorization
     */
    public function authorizeCardType(Card $card): Authorization
    {
        $this->assertNull($card->getPayment());
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO, 'http://vnexpress.vn');
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertInstanceOf(Payment::class, $authorization->getPayment());
        $this->assertNotEmpty($authorization->getPayment()->getId());
        $this->assertNotEmpty($authorization->getPayment()->getRedirectUrl());
        $this->assertSame($authorization, $card->getPayment()->getAuthorization());

        echo "\nAuthorizationId: " . $authorization->getId();
        echo "\nPaymentId: " . $authorization->getPayment()->getId();
        return $authorization;
    }

    /**
     * @param Card $card
     * @depends createCardType
     * @test
     */
    public function chargeCardType(Card $card)
    {
        $charge = $card->charge(1.0, Currency::EUROPEAN_EURO, 'http://vnexpress.vn');



        $this->assertNotNull($charge);
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
