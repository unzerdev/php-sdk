<?php
/**
 * This class defines integration tests to verify cancellation of charges.
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
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class CancelAfterChargeTest extends BasePaymentTest
{
    /**
     * Verify charge can be fetched by id.
     *
     * @test
     *
     * @return Charge
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeShouldBeFetchable(): Charge
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);
        $fetchedCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());

        $chargeArray = $charge->expose();
        $this->assertEquals($chargeArray, $fetchedCharge->expose());

        return $charge;
    }

    /**
     * Verify full refund of a charge.
     *
     * @test
     * @depends chargeShouldBeFetchable
     *
     * @param Charge $charge
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeShouldBeFullyRefundable(Charge $charge)
    {
        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelCharge($charge);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());
    }

    /**
     * Verify full refund of a charge.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeShouldBeFullyRefundableWithId()
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());
    }

    /**
     * Verify partial refund of a charge.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeShouldBePartlyRefundableWithId()
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        $firstPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelChargeById($charge->getPayment()->getId(), $charge->getId(), 10.0);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $secondPayment = $this->heidelpay->fetchPayment($refund->getPayment()->getId());
        $this->assertNotNull($secondPayment);
        $this->assertAmounts($secondPayment, 0, 90, 100, 10);
        $this->assertTrue($secondPayment->isCompleted());
    }

    /**
     * Verify partial refund of a charge.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function chargeShouldBePartlyRefundable()
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);

        $firstPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelCharge($charge, 10.0);
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
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelShouldAcceptPaymentReferenceParameter()
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0000, 'EUR', $paymentType, self::RETURN_URL);
        $cancel = $charge->cancel(null, null, 'myPaymentReference');
        $this->assertEquals('myPaymentReference', $cancel->getPaymentReference());
    }
}
