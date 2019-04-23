<?php
/**
 * This class defines unit tests to verify functionality of the HasPaymentState trait.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Traits;

use heidelpayPHP\Constants\PaymentState;
use heidelpayPHP\test\BaseUnitTest;
use RuntimeException;

class HasPaymentStateTest extends BaseUnitTest
{
    /**
     * Verify that getters and setters work properly.
     *
     * @test
     * @dataProvider gettersAndSettersShouldWorkProperlyDP
     *
     * @throws RuntimeException
     *
     * @param mixed $state
     * @param mixed $stateName
     * @param mixed $pending
     * @param mixed $completed
     * @param mixed $canceled
     * @param mixed $partlyPaid
     * @param mixed $paymentReview
     * @param mixed $chargeBack
     */
    public function gettersAndSettersShouldWorkProperly(
        $state,
        $stateName,
        $pending,
        $completed,
        $canceled,
        $partlyPaid,
        $paymentReview,
        $chargeBack
    ) {
        $traitDummy = new TraitDummyHasCancellationsHasPaymentState();
        $this->assertEquals(PaymentState::STATE_PENDING, $traitDummy->getState());
        $this->assertEquals(PaymentState::STATE_NAME_PENDING, $traitDummy->getStateName());
        $this->assertTrue($traitDummy->isPending());
        $this->assertFalse($traitDummy->isCompleted());
        $this->assertFalse($traitDummy->isCanceled());
        $this->assertFalse($traitDummy->isPartlyPaid());
        $this->assertFalse($traitDummy->isPaymentReview());
        $this->assertFalse($traitDummy->isChargeBack());

        $traitDummy->setState($state);
        $this->assertEquals($state, $traitDummy->getState());
        $this->assertEquals($stateName, $traitDummy->getStateName());
        $this->assertEquals($pending, $traitDummy->isPending());
        $this->assertEquals($completed, $traitDummy->isCompleted());
        $this->assertEquals($canceled, $traitDummy->isCanceled());
        $this->assertEquals($partlyPaid, $traitDummy->isPartlyPaid());
        $this->assertEquals($paymentReview, $traitDummy->isPaymentReview());
        $this->assertEquals($chargeBack, $traitDummy->isChargeBack());
    }

    //<editor-fold desc="Data providers">

    /**
     * Returns test data for gettersAndSettersShouldWorkProperly.
     *
     * @return array
     */
    public function gettersAndSettersShouldWorkProperlyDP(): array
    {
        return [
            'pending'        => [
                PaymentState::STATE_PENDING,
                PaymentState::STATE_NAME_PENDING,
                true,
                false,
                false,
                false,
                false,
                false
            ],
            'completed'      => [
                PaymentState::STATE_COMPLETED,
                PaymentState::STATE_NAME_COMPLETED,
                false,
                true,
                false,
                false,
                false,
                false
            ],
            'canceled'       => [
                PaymentState::STATE_CANCELED,
                PaymentState::STATE_NAME_CANCELED,
                false,
                false,
                true,
                false,
                false,
                false
            ],
            'partly_paid'    => [
                PaymentState::STATE_PARTLY,
                PaymentState::STATE_NAME_PARTLY,
                false,
                false,
                false,
                true,
                false,
                false
            ],
            'payment_review' => [
                PaymentState::STATE_PAYMENT_REVIEW,
                PaymentState::STATE_NAME_PAYMENT_REVIEW,
                false,
                false,
                false,
                false,
                true,
                false
            ],
            'chargeback'     => [
                PaymentState::STATE_CHARGEBACK,
                PaymentState::STATE_NAME_CHARGEBACK,
                false,
                false,
                false,
                false,
                false,
                true
            ]
        ];
    }

    //</editor-fold>
}
