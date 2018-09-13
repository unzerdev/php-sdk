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

use heidelpay\NmgPhpSdk\Constants\ApiResponseCodes;
use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class CardTest extends BasePaymentTest
{
    //<editor-fold desc="Required Tests">
    /**
     * @test
     */
    public function createCardWithMerchantNotPCIDSSCompliantShouldThrowException()
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_INSUFFICIENT_PERMISSIONS);

        /** @var Heidelpay $heidelpay */
        $heidelpay = new Heidelpay(self::PRIVATE_KEY_NOT_PCI_DDS_COMPLIANT);

        $card = $this->createCard();
        $this->assertNull($card->getId());
        $heidelpay->createPaymentType($card);
        $this->assertNotNull($card->getId());
    }

    /**
     * @test
     */
    public function cardShouldBeCreatable()
    {
        $card = $this->createCard();
        $this->assertNull($card->getId());
        $card = $this->heidelpay->createPaymentType($card);

        /** @var HeidelpayResourceInterface $card */
        $this->assertNotNull($card->getId());

        /** @var HeidelpayParentInterface $card */
        $this->assertSame($this->heidelpay, $card->getHeidelpayObject());

        return $card;
    }

    /**
     * The card can perform an authorization.
     *
     * @test
     */
    public function cardCanPerformAuthorizationAndCreatesPayment()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        /** @var Authorization $authorization */
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);

        $this->assertNotNull($authorization->getId());
        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());
    }
    //</editor-fold>

    //<editor-fold desc="Tests">

    /**
     * The payment type can be set in the payment object directly.
     *
     * @test
     */
    public function authorizeWithoutPaymentType()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());

        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $authorization = $payment->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);

        /** @var Payment $actualPayment */
        $actualPayment = $authorization->getPayment();
        $this->assertSame($payment, $actualPayment);
        $this->assertSame($card, $actualPayment->getPaymentType());
    }

//    /**
//     * @param Card $card
//     * @depends createCardType
//     * @test
//     * @return Authorization
//     */
//    public function authorizeCardType(Card $card): Authorization
//    {
//        $payment = $this->createPayment();
//        $this->assertEmpty($payment->getId());
//
//        $payment->setPaymentType($card);
//        $this->assertSame($card, $payment->getPaymentType());
//
//        $authorization = $payment->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
//        $this->assertNotNull($authorization);
//
//        // verify the objects have been updated
//        $this->assertNotEmpty($authorization->getId());
//        $this->assertNotEmpty($payment->getId());
//
//        // verify payment and paymentType are linked properly
//        $this->assertSame($payment, $authorization->getPayment());
//        $this->assertSame($authorization, $payment->getAuthorization());
//
//        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
//        $this->assertTrue($payment->isPending());
//
//        echo "\nAuthorizationId: " . $authorization->getId();
//        echo "\nPaymentId: " . $payment->getId();
//
//        return $authorization;
//    }

    /**
     * Should throw MissingResourceException if the paymentType has not been set prior to payment transaction.
     *
     * @test
     */
    public function shouldThrowExceptionIfNoPaymentTypeIsSetPriorToTransaction()
    {
        $this->expectException(MissingResourceException::class);

        $payment = $this->createPayment();
        $payment->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * @test
     * @return Charge
     */
    public function chargeCardType(): Charge
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());

        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $this->assertEmpty($payment->getId());

        $charge = $payment->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertNotNull($charge);

        // verify the objects have been updated
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($payment->getId());

        // verify payment and paymentType are linked properly
        $this->assertSame($payment, $charge->getPayment());
        $this->assertArraySubset([$charge->getId() => $charge], $payment->getCharges());

        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        echo "\nChargeId: " . $charge->getId();
        echo "\nPaymentId: " . $payment->getId();
        return $charge;
	}

    /**
     * @test
     */
	public function fullChargeWithoutAuthorizeShouldThrowException()
	{
        $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();

        $this->expectException(MissingResourceException::class);
	    $payment->charge();
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
        $payment = $this->createPayment();
        $payment->setPaymentType($card);

        $payment->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
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
     *
     * todo: add test do verify charge without authorization and currency failes. Currency can only be omitted if an authorization has been done before.
     */
    public function partialAndFullChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);

        $payment->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
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
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $payment->charge(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        echo 'Payment: ' . $payment->getId();

        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $payment->cancel();
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Full cancel on authorization without charges or cancels.
     *
     * @test
     */
    public function fullCancelOnAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $authorization = $payment->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $cancellation = $authorization->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Full cancel on partly charged authorization.
     *
     * @test
     */
    public function fullCancelOnPartlyChargedAuthorization()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = new Payment($this->heidelpay);
        $payment->setPaymentType($card);

        $authorization = $payment->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
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

//    /**
//     * Full cancel on fully charged authorization.
//     *
//     * @test
//     */
//    public function fullCancelOnFullyChargedAuthorization()
//    {
//        /** @var Card $card */
//        $card = $this->heidelpay->createPaymentType($this->createCard());
//        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
//        $payment = $authorization->getPayment();
//        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPending());
//
//        $payment->charge(10.0);
//        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPartlyPaid());
//
//        $payment->charge(90.0);
//        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
//        $this->assertTrue($payment->isCompleted());
//
//        $cancellation = $authorization->cancel();
//        $this->assertNotEmpty($cancellation);
//        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 10.0);
//        $this->assertTrue($payment->isCanceled());
//    }

    /**
     * Full cancel on fully charged payment.
     *
     * @test
     */
    public function fullCancelOnFullyChargedPayment()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = new Payment($this->heidelpay);
        $payment->setPaymentType($card);

        $payment->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(90.0);
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $cancellation = $payment->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Full cancel on partly charged auth canceled charges.
     *
     * @test
     */
    public function fullCancelOnPartlyPaidAuthWithCanceledCharges()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = new Payment($this->heidelpay);
        $payment->setPaymentType($card);

        $authorization = $payment->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
        $charge = $payment->charge(10.0);
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $charge->cancel();
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 10.0);
        $this->assertTrue($payment->isPartlyPaid());

        $authorization->cancel();
        $this->assertTrue($payment->isCanceled());
    }

//    /**
//     * @test
//     */
//    public function ()
//    {
//        $card = new Card('', '');
//        $card->setId('s-car-1');
//        $this->heidelpay->setPaymentType($card);
//        $card->fetch();
//        $card = $this->heidelpay->fetchCard()->authorize()
//    }

//    /**
//     * Partly cancel on fully charged auth.
//     *
//     * @test
//     */
//    public function partlyCancelOnFullyChargedAuth()
//    {
//        /** @var Card $card */
//        $card = $this->heidelpay->createPaymentType($this->createCard());
//        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
//        $payment = $authorization->getPayment();
//        $payment->charge(10.0);
//        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
//        $charge = $payment->charge(90.0);
//        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
//        $this->assertTrue($payment->isCompleted());
//
//        $charge->cancel();
//
//        $authorization->cancel();
//        $this->assertTrue($payment->isCanceled());
//    }



    // PaymentState = cancelled --> https://heidelpay.atlassian.net/wiki/spaces/ID/pages/118030386/payments

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
