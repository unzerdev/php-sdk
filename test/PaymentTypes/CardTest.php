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
        // todo: check payment has been updated
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
        // todo: check payment has been updated
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
        // todo: check payment has not been updated
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
        // todo: check payment has been updated
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
        $payment->charge(); // todo: das macht die api selber, ich muss den remainder nicht ermitteln
        $this->assertEquals(0.0, $payment->getRemainingAmount());
        // todo: check payment has been updated
    }

    /**
     * @test
     */
    public function fullCancelAfterCharge()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $charge = $card->charge(100.00, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $charge->getPayment();
        $this->assertEquals(0.0, $payment->getRemainingAmount());
        $this->assertEquals(100.00, $payment->getChargedAmount());
        $payment->cancel();
        $this->assertEquals(0.00, $payment->getChargedAmount());
        // todo: check payment has been updated
    }

    /**
     * @test
     */
    public function fullCancelOnAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $cancellation = $authorization->cancel();
        $this->assertNotEmpty($cancellation);
        // todo: check payment has been updated

    }

    /**
     * @test
     */
    public function fullCancelOnPartlyChargedAuthorization()
    {

    }

    // fullCancel on Auth w/o charges and cancels
    // PaymentState = cancelled --> https://heidelpay.atlassian.net/wiki/spaces/ID/pages/118030386/payments
    // cancel authorization

    // fullCancel on fully charged Auth
    // canceln der einzelnen charges

    // fullCancel on partly Charged Auth
    // canceln der einzelnen charges

    // fullCancel on partly Charged and partly canceled Auth
    // alle charges, die nicht gecancelled sind canceln

    // PartCancel on fully charged Auth
    // cancel auf den charge mit dem betrag
    // $payment->charge['key']->cancel(30) //todo bei arrays id als key

    // PartCancel on Auth w/o charges and cancels
    // $payment->auth->cancel(amount)

    // PartCancel on Auth w charges w/o cancels
    // fall-1: auth = 100, charge 60 , cancel = 40, state completed
    // fall-2: auth = 100, charged 60, cancel = 60, exception von der api

    // PartCancel on Auth o charges w cancels
    // s.o.

    // Auth = 100, cha: 60, auth.can = 40 = remaining=0; charge.cancel(60) -> auth.state = canceled

    // Speichere ich die cancels immer direkt in den charges?
    // muss immer genau ein charge gecancelled werden?

    // Berechnung in der api amounts nicht selber berechnen, sondern aus der api holen
    // nur payment updaten, wenn es benutzt wird


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
