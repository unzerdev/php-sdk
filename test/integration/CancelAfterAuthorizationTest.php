<?php
/**
 * This class defines integration tests to verify cancellation of authorizations.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class CancelAfterAuthorizationTest extends BasePaymentTest
{
    /**
     * Verify that a full cancel on an authorization results in a cancelled payment.
     *
     * @test
     */
    public function fullCancelOnAuthorization()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $this->heidelpay->authorize(100.0000, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);

        /** @var Authorization $fetchedAuthorization */
        $fetchedAuthorization = $this->heidelpay->fetchAuthorization($authorization->getPayment()->getId());
        $payment = $fetchedAuthorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $cancellation = $fetchedAuthorization->cancel();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
        $this->assertTrue($payment->isCanceled());
    }

//    /**
//     * Verify partly cancel on an authorization.
//     *
//     * @test
//     */
//    public function partCancelOnAuthorize()
//    {
//        $card = $this->heidelpay->createPaymentType($this->createCard());
//        $authorization = $this->heidelpay->authorize(100.0000, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);
//        $payment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());
//
//
//    }


//    /**
//     * Verify a full cancel can be performed on a partly charged card authorization.
//     *
//     * @test
//     */
//    public function fullCancelOnPartlyChargedAuthorization()
//    {
//        $card = $this->heidelpay->createPaymentType($this->createCard());
//        $authorization = $this->heidelpay->authorize(100.0000, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);
//        $payment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());
//
//
//
//        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPending());
//
//        $payment->charge(10.0);
//        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPartlyPaid());
//
//        $cancellation = $authorization->cancel();
//        $this->assertNotEmpty($cancellation);
//        $this->assertAmounts($payment, 0.0, 10.0, 10.0, 0.0);
//        $this->assertTrue($payment->isCompleted());
//    }

//    /**
//     * Verify an exception is thrown when trying to charge an already fully charged authorization.
//     *
//     * @test
//     */
//    public function fullCancelOnFullyChargedAuthorizationThrowsException()
//    {
//        /** @var Card $card */
//        $card = $this->createCard();
//        $card = $this->heidelpay->createPaymentType($card);
//        $authorization = $card->authorize(100.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
//        $payment = $authorization->getPayment();
//        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
//        $this->assertTrue($payment->isPending());
//
//        $payment->charge(100.0);
//        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
//        $this->assertTrue($payment->isCompleted());
//
//        $this->expectException(HeidelpayApiException::class);
//        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ALREADY_CHARGED);
//        $authorization->cancel();
//    }
}
