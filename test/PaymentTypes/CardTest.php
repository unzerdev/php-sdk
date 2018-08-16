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
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\AbstractPaymentTest;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class CardTest extends AbstractPaymentTest
{
    const RETURN_URL = 'http://vnexpress.vn';

    /**
     * @test
     */
    //<editor-fold desc="Tests">
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
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertInstanceOf(Payment::class, $authorization->getPayment());
        $this->assertNotEmpty($authorization->getPayment()->getId());
        $this->assertSame($authorization, $card->getPayment()->getAuthorization());

        echo "\nAuthorizationId: " . $authorization->getId();
        echo "\nPaymentId: " . $authorization->getPayment()->getId();
        return $authorization;
    }

    /**
     * @test
     * @return Charge
     */
    public function chargeCardType(): Charge
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());

        $this->assertNull($card->getPayment());
        $charge = $card->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertArraySubset([$charge], $card->getPayment()->getCharges());

        echo "\nChargeId: " . $charge->getId();
        echo "\nPaymentId: " . $charge->getPayment()->getId();
        return $charge;
	}

    /**
     * @test
     */
	public function fullChargeWithoutAuthorizeShouldThrowException()
	{
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
	    $this->expectException(MissingResourceException::class);
	    $card->charge();
	}

    /**
     * @test
     * @depends authorizeCardType
     * @param Authorization $authorization
     */
	public function fullChargeAfterAuthorize(Authorization $authorization)
	{
	    $this->assertGreaterThan(0.0, $authorization->getPayment()->getRemainingAmount());
        $authorization->getPayment()->fullCharge();
        $this->assertEquals(0.0, $authorization->getPayment()->getRemainingAmount());
    }

    /**
     * @test
     */
    public function partialChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertEquals(100.0, $payment->getRemainingAmount());
        $payment->charge(20);
        $this->assertEquals(80.0, $payment->getRemainingAmount());
        $payment->charge(20);
        $this->assertEquals(60.0, $payment->getRemainingAmount());
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The amount of 70.0000 to be charged exceeds the authorized amount of 60.0000');
        $this->expectExceptionCode('API.330.100.007');
        $payment->charge(70);
        $payment->charge(60);
        $this->assertEquals(0.0, $payment->getRemainingAmount());
    }

    /**
     * @test
     */
    public function partialAndFullChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertEquals(100.0, $payment->getRemainingAmount());
        $payment->charge(20);
        $this->assertEquals(80.0, $payment->getRemainingAmount());
        $payment->charge();
        $this->assertEquals(0.0, $payment->getRemainingAmount());
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">
    /**
     * @return Card
     */
    private function createCard(): Card
    {
        /** @var Card $card */
        $card = new Card ('4012888888881881', '03/20');
        $card->setCvc('123');
        return $card;
    }
    //</editor-fold>
}
