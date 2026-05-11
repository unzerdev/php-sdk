<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify functionality of the Payment charge method.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\Constants\CancelReasonCodes;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class PaymentCancelTest extends BaseIntegrationTest
{
    //<editor-fold desc="Tests">

    /**
     * Verify full cancel on cancelled authorize returns empty array.
     *
     * @test
     */
    public function doubleCancelOnAuthorizeShouldReturnEmptyArray(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $cancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
        $this->assertCount(1, $cancellations);

        $newCancellations = $payment->cancelAmount();
        $this->assertCount(0, $newCancellations);
    }

    /**
     * Verify full cancel on charge.
     * AND
     * Return empty array if charge is already fully cancelled.
     * PHPLIB-228 - Case 1 + double cancel
     *
     * @test
     */
    public function cancelOnChargeAndDoubleCancel(): void
    {
        $this->useLegacyKey();
        $charge = $this->createCharge(123.44);
        $payment = $this->unzer->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0.0);

        $cancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
        $this->assertCount(1, $cancellations);

        $payment = $this->unzer->fetchPayment($charge->getPaymentId());
        $newCancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
        $this->assertCount(0, $newCancellations);
    }

    /**
     * Verify full cancel on multiple charges.
     * PHPLIB-228 - Case 2
     *
     * @test
     */
    public function fullCancelOnPaymentWithAuthorizeAndMultipleChargesShouldBePossible(): void
    {
        $authorization = $this->createCardAuthorization(123.44);
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 123.44, 0.0, 123.44, 0.0);

        $charge1 = $this->unzer->performChargeOnPayment($payment, new Charge(100.44));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, 123.44, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $charge2 = $this->unzer->performChargeOnPayment($payment, new Charge(23.00));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount());
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);

        $allCancellations = $payment->getCancellations();
        $this->assertCount(2, $allCancellations);
        $this->assertEquals($charge1->getId(), $allCancellations[0]->getParentResource()->getId());
        $this->assertEquals($charge2->getId(), $allCancellations[1]->getParentResource()->getId());
    }

    /**
     * Verify partial cancel on charge.
     * PHPLIB-228 - Case 3
     *
     * @test
     */
    public function partialCancelAndFullCancelOnSingleCharge(): void
    {
        $this->useLegacyKey();
        $charge = $this->createCharge(222.33);
        $payment = $this->unzer->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 222.33, 222.33, 0.0);

        $this->assertCount(1, $payment->cancelAmount(123.12));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 99.21, 222.33, 123.12);

        $payment = $this->unzer->fetchPayment($charge->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(99.21));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 222.33, 222.33);
    }

    /**
     * Verify partial cancel on multiple charges (cancel < last charge).
     * PHPLIB-228 - Case 4 + 5
     *
     * @test
     *
     * @dataProvider partCancelDataProvider
     *
     * @param float $amount
     * @param int   $numberCancels
     */
    public function partialCancelOnMultipleChargedAuthorization($amount, $numberCancels): void
    {
        $authorizeAmount = 123.44;
        $authorization = $this->createCardAuthorization($authorizeAmount);
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());

        $this->unzer->performChargeOnPayment($payment, new Charge(23.00));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 100.44, 23.0, $authorizeAmount, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->unzer->performChargeOnPayment($payment, new Charge(100.44));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount, $authorizeAmount, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount($numberCancels, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount - $amount, $authorizeAmount, $amount);
    }

    /**
     * Verify full cancel on authorize.
     * PHPLIB-228 - Case 6
     *
     * @test
     *
     * @dataProvider fullCancelDataProvider
     *
     * @param float $amount
     */
    public function fullCancelOnAuthorize($amount): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * Verify partial cancel on authorize.
     * PHPLIB-228 - Case 7
     *
     * @test
     */
    public function fullCancelOnPartCanceledAuthorize(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(10.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 90.0, 0.0, 90.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(10.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 80.0, 0.0, 80.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount());
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * Verify full cancel on fully charged authorize.
     * PHPLIB-228 - Case 8
     *
     * @test
     *
     * @dataProvider fullCancelDataProvider
     *
     * @param float $amount The amount to be cancelled.
     */
    public function fullCancelOnFullyChargedAuthorize($amount): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->unzer->performChargeOnPayment($payment, new Charge());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
    }

    /**
     * Verify full cancel on partly charged authorize.
     * PHPLIB-228 - Case 9
     *
     * @test
     *
     * @dataProvider fullCancelDataProvider
     *
     * @param $amount
     */
    public function fullCancelOnPartlyChargedAuthorizeShouldBePossible($amount): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->unzer->performChargeOnPayment($payment, new Charge(50.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
    }

    /**
     * Verify part cancel on uncharged authorize.
     * PHPLIB-228 - Case 10
     *
     * @test
     */
    public function partCancelOnUnchargedAuthorize(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(50.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 50.0, 0.0, 50.0, 0.0);
    }

    /**
     * Verify part cancel on partly charged authorize with cancel amount lt charged amount.
     * PHPLIB-228 - Case 11
     *
     * @test
     */
    public function partCancelOnPartlyChargedAuthorizeWithAmountLtCharged(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->unzer->performChargeOnPayment($payment, new Charge(25.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 75.0, 25.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(20.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 55.0, 25.0, 80.0, 0.0);
    }

    /**
     * Verify part cancel on partly charged authorize with cancel amount gt charged amount.
     * PHPLIB-228 - Case 12
     *
     * @test
     */
    public function partCancelOnPartlyChargedAuthorizeWithAmountGtCharged(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->unzer->performChargeOnPayment($payment, new Charge(40.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 60.0, 40.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount(80.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 20.0, 40.0, 20.0);
    }

    /**
     * Verify cancelling more than was charged.
     * PHPLIB-228 - Case 15
     *
     * @test
     */
    public function cancelMoreThanWasCharged(): void
    {
        $this->useLegacyKey();
        $charge = $this->createCharge(50.0);
        $payment = $this->unzer->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 50.0, 50.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(100.0));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
    }

    /**
     * Verify second cancel on partly cancelled charge.
     * PHPLIB-228 - Case 16
     *
     * @test
     */
    public function secondCancelExceedsRemainderOfPartlyCancelledCharge(): void
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->unzer->performChargeOnPayment($payment, new Charge(50.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->unzer->performChargeOnPayment($payment, new Charge(50.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(40.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 60.0, 100.0, 40.0);

        $payment = $this->unzer->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount(30.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 30.0, 100.0, 70.0);
    }

    /**
     * Verify cancellation with all parameters set.
     *
     * @test
     */
    public function cancellationShouldWorkWithAllParametersSet(): void
    {
        $authorization = $this->createCardAuthorization(119.0);
        $payment = $authorization->getPayment();
        $this->unzer->performChargeOnPayment($payment, new Charge());
        $cancellations = $payment->cancelAmount(59.5, CancelReasonCodes::REASON_CODE_CREDIT, 'Reference text!', 50.0, 9.5);
        $this->assertCount(1, $cancellations);
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function partCancelDataProvider(): array
    {
        return [
            'cancel amount lt last charge' => [20, 1],
            'cancel amount eq to last charge' => [23, 1],
            'cancel amount gt last charge' => [40, 2]
        ];
    }

    /**
     * @return array
     */
    public function fullCancelDataProvider(): array
    {
        return [
            'no amount given' => [null],
            'amount given' => [100.0]
        ];
    }

    //</editor-fold>
}
