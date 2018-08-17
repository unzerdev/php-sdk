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
use heidelpay\NmgPhpSdk\test\BasePaymentTest;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class CardTest extends BasePaymentTest
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

        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        echo "\nAuthorizationId: " . $authorization->getId();
        echo "\nPaymentId: " . $payment->getId();

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
        $this->assertArraySubset([$charge->getId() => $charge], $card->getPayment()->getCharges());

        $payment = $charge->getPayment();
        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());

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
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->fullCharge();
        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());
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
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(20);
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(20);
        $this->assertAmounts($payment, 60.0, 40.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The amount of 70.0000 to be charged exceeds the authorized amount of 60.0000');
        $this->expectExceptionCode('API.330.100.007');
        $payment->charge(70);
        $this->assertAmounts($payment, 60.0, 40.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(60);
        $this->assertAmounts($payment, 00.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());
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
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(20);
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge();
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * @test
     */
    public function fullCancelAfterCharge()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $charge = $card->charge(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);

        $payment = $charge->getPayment();
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $payment->cancel();
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * @test
     */
    public function fullCancelOnAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $cancellation = $authorization->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * @test
     */
    public function fullCancelOnPartlyChargedAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $cancellation = $authorization->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 10.0, 10.0, 10.0);
        $this->assertTrue($payment->isCanceled());
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
}
