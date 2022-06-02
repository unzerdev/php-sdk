<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify cancellation in general.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\integration\TransactionTypes
 */
namespace UnzerSDK\test\integration\TransactionTypes;

use UnzerSDK\test\BaseIntegrationTest;

class CancelTest extends BaseIntegrationTest
{
    /**
     * Verify reversal is fetchable.
     *
     * @test
     */
    public function reversalShouldBeFetchableViaUnzerObject(): void
    {
        $authorization = $this->createCardAuthorization();
        $cancel = $authorization->cancel();
        $fetchedCancel = $this->unzer->fetchReversal($authorization->getPayment()->getId(), $cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify reversal is fetchable.
     *
     * @test
     */
    public function reversalShouldBeFetchableViaPaymentObject(): void
    {
        $authorization = $this->createCardAuthorization();
        $cancel = $authorization->cancel();
        $fetchedCancel = $cancel->getPayment()->getAuthorization()->getCancellation($cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify refund is fetchable.
     *
     * @test
     */
    public function refundShouldBeFetchableViaUnzerObject(): void
    {
        $charge = $this->createCharge();
        $cancel = $charge->cancel();
        $fetchedCancel = $this->unzer->fetchRefundById($charge->getPayment()->getId(), $charge->getId(), $cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify refund is fetchable.
     *
     * @test
     */
    public function refundShouldBeFetchableViaPaymentObject(): void
    {
        $charge = $this->createCharge();
        $cancel = $charge->cancel();
        $fetchedCancel = $cancel->getPayment()->getCharge($charge->getId())->getCancellation($cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify reversal is fetchable via payment object.
     *
     * @test
     */
    public function authorizationCancellationsShouldBeFetchableViaPaymentObject(): void
    {
        $authorization = $this->createCardAuthorization();
        $reversal = $authorization->cancel();
        $fetchedPayment = $this->unzer->fetchPayment($authorization->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertTransactionResourceHasBeenCreated($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }

    /**
     * Verify refund is fetchable via payment object.
     *
     * @test
     */
    public function chargeCancellationsShouldBeFetchableViaPaymentObject(): void
    {
        $charge = $this->createCharge();
        $reversal = $charge->cancel();
        $fetchedPayment = $this->unzer->fetchPayment($charge->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertTransactionResourceHasBeenCreated($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }

    /**
     * Verify transaction status.
     *
     * @test
     */
    public function cancelStatusIsSetCorrectly(): void
    {
        $charge = $this->createCharge();
        $reversal = $charge->cancel();
        $this->assertSuccess($reversal);
    }
}
