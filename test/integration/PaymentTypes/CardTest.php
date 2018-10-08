<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the card payment methods e.g. Credit Card and Debit Card.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

class CardTest extends BasePaymentTest
{
    //<editor-fold desc="Tests">
    /**
     * Verify that direct card creation is not possible if the merchant is not PCI DSS compliant.
     * In this case he needs to use the iFrame or needs to be marked PCI DSS compliant in the payment backend.
     *
     * @test
     */
    public function createCardWithMerchantNotPCIDSSCompliantShouldThrowException()
    {
        $this->heidelpay->setKey(self::PRIVATE_KEY_NOT_PCI_DDS_COMPLIANT);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_INSUFFICIENT_PERMISSIONS);
        $card = $this->createCard();
        $this->assertNull($card->getId());
        $this->heidelpay->createPaymentType($card);
        $this->assertNotNull($card->getId());
    }

    /**
     * Verify that card payment type resource can be created.
     *
     * @test
     */
    public function cardShouldBeCreatable()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $this->assertNull($card->getId());
        $card = $this->heidelpay->createPaymentType($card);

        $this->assertInstanceOf(Card::class, $card);
        /** @var HeidelpayResourceInterface $card */
        $this->assertNotNull($card->getId());
        /** @var HeidelpayParentInterface $card */
        $this->assertSame($this->heidelpay, $card->getHeidelpayObject());

        return $card;
    }

    /**
     * Verify that the card can perform an authorization with a card.
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
     * Verify the card can perform charges and creates a payment object doing so.
     *
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
     * Verify that a card object can be fetched from the api using its id.
     *
     * @test
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
        $this->assertEquals($this->maskCreditCardNumber($card->getNumber()), $fetchedCard->getNumber());
        $this->assertEquals($card->getExpiryDate(), $fetchedCard->getExpiryDate());
        $this->assertEquals('***', $fetchedCard->getCvc());
    }

    /**
     * Verify the card can charge the full amount of the authorization and the payment state is updated accordingly.
     *
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

        /** @var Charge $charge */
        $charge = $this->heidelpay->chargeAuthorization($payment->getId());
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify the card can charge part of the authorized amount and the payment state is updated accordingly.
     *
     * @test
     */
    public function partialChargeAfterAuthorization()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $this->heidelpay->authorize(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);

        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 20);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 20);
        $payment2 = $charge->getPayment();
        $this->assertAmounts($payment2, 60.0, 40.0, 100.0, 0.0);
        $this->assertTrue($payment2->isPartlyPaid());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 60);
        $payment3 = $charge->getPayment();
        $this->assertAmounts($payment3, 00.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment3->isCompleted());
    }

    /**
     * Verify that an exception is thrown when trying to charge more than authorized.
     *
     * @test
     */
    public function exceptionShouldBeThrownWhenChargingMoreThenAuthorized()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 50);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 50.0, 50.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED);
        $this->heidelpay->chargeAuthorization($payment->getId(), 70);
    }

    /**
     * Verify the card payment can be charged until it is fully charged and the payment is updated accordingly.
     *
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

        $charge = $this->heidelpay->chargeAuthorization($payment->getId(), 20);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $charge = $this->heidelpay->chargeAuthorization($payment->getId());
        $payment2 = $charge->getPayment();
        $this->assertAmounts($payment2, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment2->isCompleted());
    }

    /**
     * Authorization can be fetched.
     *
     * @test
     */
    public function authorizationShouldBeFetchable()
    {
        /** @var Card $card */
        $card = $this->createCard();
        $card = $this->heidelpay->createPaymentType($card);
        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment = $authorization->getPayment();

        $fetchedAuthorization = $this->heidelpay->fetchAuthorization($payment->getId());
        $this->assertEquals($fetchedAuthorization->getId(), $authorization->getId());
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
     * Verify a card payment can be cancelled after being fully charged.
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

//    /**
//     * Full cancel on partly charged auth canceled charges.
//     *
//     * @test
//     */
//    public function fullCancelOnPartlyPaidAuthWithCanceledCharges()
//    {
//        /** @var Card $card */
//        $card = $this->createCard();
//        $card = $this->heidelpay->createPaymentType($card);
//
//        $authorization = $card->authorize(100.0000, Currency::EUROPEAN_EURO, self::RETURN_URL);
//        $payment = $authorization->getPayment();
//
//        $payment->charge(10.0);
//        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
//
//        $charge = $payment->charge(10.0);
//        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPartlyPaid());
//
//        $charge->cancel();
//        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 10.0);
//        $this->assertTrue($payment->isPartlyPaid());
//
//        $payment->cancel();
//        $this->assertTrue($payment->isCanceled());
//    }
    //</editor-fold>
}
