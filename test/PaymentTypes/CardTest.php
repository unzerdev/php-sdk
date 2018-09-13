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
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\HeidelpayResourceInterface;
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

        // verify authorization has been created
        $this->assertNotNull($authorization->getId());

        // verify payment object has been created
        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        // verify resources are linked properly
        $this->assertSame($authorization, $payment->getAuthorization());
        $this->assertSame($card, $payment->getPaymentType());

        // verify the payment object has been updated properly
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());
    }

    /**
     * @test
     */
    public function cardCanPerformChargeAndCreatesPaymentObject()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        /** @var Charge $charge */
        $charge = $card->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);

        // verify charge has been created
        $this->assertNotNull($charge->getId());

        // verify payment object has been created
        $payment = $charge->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        // verify resources are linked properly
        $this->assertArraySubset([$charge->getId() => $charge], $payment->getCharges());
        $this->assertSame($card, $payment->getPaymentType());

        // verify the payment object has been updated properly
        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * @test
     *
     * todo update number and cvc
     */
    public function cardCanBeFetched()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $this->assertNotNull($card->getId());

        /** @var Card $fetchedCard */
        $fetchedCard = $this->heidelpay->fetchPaymentType($card->getId());
        $this->assertNotNull($fetchedCard->getId());
//        $this->assertEquals($card->getNumber(), $fetchedCard->getNumber());
        $this->assertEquals($card->getExpiryDate(), $fetchedCard->getExpiryDate());
//        $this->assertEquals($card->getCvc(), $fetchedCard->getCvc());
    }
    //</editor-fold>

    //<editor-fold desc="Tests">
    /**
     * @test
     */
	public function fullChargeAfterAuthorize()
	{
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        /** @var Authorization $authorization */
        $authorization = $card->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

        // pre-check to verify changes due to fullCharge call
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->fullCharge();

        // verify payment has been updated properly
        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * @test
     */
    public function partialChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $this->heidelpay->authorize($card, 100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);

        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(20);
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(20);
        $this->assertAmounts($payment, 60.0, 40.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(60);
        $this->assertAmounts($payment, 00.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * @test
     */
    public function exceptionShouldBeThrownWhenChargingMoreThenAuthorized()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $this->heidelpay->authorize($card, 100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);

        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(50);
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED);
        $payment->charge(70);
    }

    /**
     * @test
     */
    public function partialAndFullChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
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
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $charge = $card->charge(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $charge->getPayment();

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
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

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
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $cancellation = $authorization->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 10.0, 10.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * Full cancel on fully charged authorization.
     *
     * @test
     */
    public function fullCancelOnFullyChargedAuthorizationThrowsException()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(100.0);
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ALREADY_CHARGED);
        $authorization->cancel();
    }

    /**
     * Full cancel on fully charged payment.
     *
     * @test
     */
    public function fullCancelOnFullyChargedPayment()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

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
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);

        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

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
    //</editor-fold>
}
