<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Cancellation transaction type.
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
 * @package  UnzerSDK\test\unit
 */

namespace UnzerSDK\test\unit\Resources\TransactionTypes;

use UnzerSDK\Constants\CancelReasonCodes;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\test\BasePaymentTest;
use PHPUnit\Framework\MockObject\MockObject;

class CancellationTest extends BasePaymentTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): void
    {
        $cancellation = new Cancellation();
        $this->assertNull($cancellation->getAmount());
        $this->assertEmpty($cancellation->getReasonCode());
        $this->assertEmpty($cancellation->getPaymentReference());
        $this->assertNull($cancellation->getAmountNet());
        $this->assertNull($cancellation->getAmountVat());

        $cancellation = new Cancellation(123.4);
        $this->assertEquals(123.4, $cancellation->getAmount());
        $this->assertEmpty($cancellation->getReasonCode());

        $cancellation->setAmount(567.8)->setAmountNet(234.5)->setAmountVat(123.4);
        $this->assertEquals(567.8, $cancellation->getAmount());
        $this->assertEquals(234.5, $cancellation->getAmountNet());
        $this->assertEquals(123.4, $cancellation->getAmountVat());

        $cancellation->setPaymentReference('my Payment Reference');
        $this->assertEquals('my Payment Reference', $cancellation->getPaymentReference());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_CANCEL);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_CANCEL, $cancellation->getReasonCode());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_CREDIT);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_CREDIT, $cancellation->getReasonCode());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_RETURN);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_RETURN, $cancellation->getReasonCode());

        $cancellation->setReasonCode(null);
        $this->assertNull($cancellation->getReasonCode());
    }

    /**
     * Verify expose will translate amount to amountGross if payment type is Installment Secured.
     *
     * @test
     */
    public function exposeWillReplaceAmountWithAmountGross(): void
    {
        /** @var Cancellation|MockObject $cancelMock */
        $cancelMock = $this->getMockBuilder(Cancellation::class)->setMethods(['getLinkedResources'])->getMock();
        $cancelMock->setAmount('123.4');
        $this->assertEquals(['amount' => 123.4], $cancelMock->expose());

        $paymentType = (new InstallmentSecured())->setId('id');
        $cancelMock->setPayment((new Payment(new Unzer('s-priv-1234')))->setPaymentType($paymentType));
        $this->assertEquals(['amountGross' => 123.4], $cancelMock->expose());
    }
}
