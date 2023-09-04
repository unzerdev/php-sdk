<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify cancellation of charges.
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

use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class CancelAfterChargeTest extends BaseIntegrationTest
{
    protected function setUp(): void
    {
        $this->useLegacyKey();
    }

    /**
     * Verify charge can be fetched by id.
     *
     * @test
     *
     * @return Charge
     */
    public function chargeShouldBeFetchable(): Charge
    {
        $paymentType = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->unzer->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);
        $fetchedCharge = $this->unzer->fetchChargeById($charge->getPayment()->getId(), $charge->getId());

        $chargeArray = $charge->setCard3ds(false)->expose();
        $this->assertEquals($chargeArray, $fetchedCharge->expose());

        return $charge;
    }

    /**
     * Verify full refund of a charge.
     *
     * @test
     *
     * @depends chargeShouldBeFetchable
     *
     * @param Charge $charge
     */
    public function chargeShouldBeFullyRefundable(Charge $charge): void
    {
        $refund = $this->unzer->cancelCharge($charge);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $traceId = $charge->getTraceId();
        $this->assertNotEmpty($traceId);
        $this->assertSame($traceId, $charge->getPayment()->getTraceId());
    }

    /**
     * Verify full refund of a charge.
     *
     * @test
     */
    public function chargeShouldBeFullyRefundableWithId(): void
    {
        $paymentType = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->unzer->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        $refund = $this->unzer->cancelChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());
    }

    /**
     * Verify partial refund of a charge.
     *
     * @test
     */
    public function chargeShouldBePartlyRefundableWithId(): void
    {
        $paymentType = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->unzer->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        $firstPayment = $this->unzer->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        $refund = $this->unzer->cancelChargeById($charge->getPayment()->getId(), $charge->getId(), 10.0);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $secondPayment = $this->unzer->fetchPayment($refund->getPayment()->getId());
        $this->assertNotNull($secondPayment);
        $this->assertAmounts($secondPayment, 0, 90, 100, 10);
        $this->assertTrue($secondPayment->isCompleted());
    }

    /**
     * Verify partial refund of a charge.
     *
     * @test
     */
    public function chargeShouldBePartlyRefundable(): void
    {
        $paymentType = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->unzer->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        $firstPayment = $this->unzer->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        $refund = $this->unzer->cancelCharge($charge, 10.0);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $secondPayment = $refund->getPayment();
        $this->assertNotNull($secondPayment);
        $this->assertAmounts($secondPayment, 0, 90, 100, 10);
        $this->assertTrue($secondPayment->isCompleted());
    }

    /**
     * Verify payment reference can be set in cancel charge transaction aka refund.
     *
     * @test
     */
    public function cancelShouldAcceptPaymentReferenceParameter(): void
    {
        $paymentType = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->unzer->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);
        $cancel = $charge->cancel(null, null, 'myPaymentReference');
        $this->assertEquals('myPaymentReference', $cancel->getPaymentReference());
    }
}
