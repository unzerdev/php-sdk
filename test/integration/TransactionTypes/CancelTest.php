<?php
/**
 * This class defines integration tests to verify cancellation in general.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration\TransactionTypes
 */
namespace heidelpayPHP\test\integration\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class CancelTest extends BasePaymentTest
{
    /**
     * Verify reversal is fetchable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function reversalShouldBeFetchableViaHeidelpayObject()
    {
        $authorization = $this->createCardAuthorization();
        $cancel = $authorization->cancel();
        $fetchedCancel = $this->heidelpay->fetchReversal($authorization->getPayment()->getId(), $cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify reversal is fetchable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function reversalShouldBeFetchableViaPaymentObject()
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function refundShouldBeFetchableViaHeidelpayObject()
    {
        $charge = $this->createCharge();
        $cancel = $charge->cancel();
        $fetchedCancel = $this->heidelpay->fetchRefundById($charge->getPayment()->getId(), $charge->getId(), $cancel->getId());
        $this->assertTransactionResourceHasBeenCreated($fetchedCancel);
        $this->assertEquals($cancel->expose(), $fetchedCancel->expose());
    }

    /**
     * Verify refund is fetchable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function refundShouldBeFetchableViaPaymentObject()
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function authorizationCancellationsShouldBeFetchableViaPaymentObject()
    {
        $authorization = $this->createCardAuthorization();
        $reversal = $authorization->cancel();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertTransactionResourceHasBeenCreated($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }

    /**
     * Verify refund is fetchable via payment object.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeCancellationsShouldBeFetchableViaPaymentObject()
    {
        $charge = $this->createCharge();
        $reversal = $charge->cancel();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());

        $cancellation = $fetchedPayment->getCancellation($reversal->getId());
        $this->assertTransactionResourceHasBeenCreated($cancellation);
        $this->assertEquals($cancellation->expose(), $reversal->expose());
    }

    /**
     * Verify transaction status.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelStatusIsSetCorrectly()
    {
        $charge = $this->createCharge();
        $reversal = $charge->cancel();
        $this->assertSuccess($reversal);
    }
}
