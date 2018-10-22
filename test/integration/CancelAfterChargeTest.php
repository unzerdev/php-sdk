<?php
/**
 * This class defines integration tests to verify cancellation of charges.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class CancelAfterChargeTest extends BasePaymentTest
{
    /**
     * Verify charge can be fetched by id.
     *
     * @test
     *
     * @return Charge
     *
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     */
    public function chargeShouldBeFetchable(): Charge
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0000, Currencies::EURO, $card, self::RETURN_URL);
        $fetchedCharge = $this->heidelpay->fetchChargeById($charge->getPayment()->getId(), $charge->getId());

        $this->assertEquals($charge->expose(), $fetchedCharge->expose());

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
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
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
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function chargeShouldBeFullyRefundableWithId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0000, Currencies::EURO, $card, self::RETURN_URL);

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
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     */
    public function chargeShouldBePartlyRefundableWithId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0000, Currencies::EURO, $card, self::RETURN_URL);

        $firstPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelChargeById($charge->getPayment()->getId(), $charge->getId(), 10.0);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $secondPayment = $this->heidelpay->fetchPayment($refund->getPayment()->getId());
        $this->assertNotNull($secondPayment);
        $this->assertAmounts($secondPayment, 0, 100, 100, 10);
        $this->assertTrue($secondPayment->isCompleted());
    }

    /**
     * Verify partial refund of a charge.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     */
    public function chargeShouldBePartlyRefundable()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0000, Currencies::EURO, $card, self::RETURN_URL);

        $firstPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertAmounts($firstPayment, 0, 100, 100, 0);
        $this->assertTrue($firstPayment->isCompleted());

        /** @var Cancellation $refund */
        $refund = $this->heidelpay->cancelCharge($charge, 10.0);
        $this->assertNotNull($refund);
        $this->assertNotEmpty($refund->getId());

        $secondPayment = $refund->getPayment();
        $this->assertNotNull($secondPayment);
        $this->assertAmounts($secondPayment, 0, 100, 100, 10);
        $this->assertTrue($secondPayment->isCompleted());
    }
}
