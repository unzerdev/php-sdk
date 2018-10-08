<?php
/**
 * This class defines integration tests to verify general functionalities of this SDK.
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class GeneralTests extends BasePaymentTest
{
    /**
     * @test
     */
    public function heidelpayObjectShouldThrowExceptionWhenKeyIsPublic()
    {
        $this->expectException(IllegalKeyException::class);
        $this->heidelpay = new Heidelpay(BasePaymentTest::PUBLIC_KEY);
    }

    /**
     * @test
     */
    public function shouldFetchPayment()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $payment->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $payment->charge(5.0);
        $this->assertAmounts($payment, 7, 5, 12, 0);

        $secondPayment = $this->createPayment();
        $this->assertEmpty($secondPayment->getId());
        $this->assertAmounts($secondPayment, 0, 0, 0, 0);
        $this->assertTrue($secondPayment->isPending());
        $secondPayment->setId($payment->getId());
        $secondPayment->fetch();
        $this->assertAmounts($secondPayment, 7, 5, 12, 0);
        $this->assertTrue($secondPayment->isPartlyPaid());
    }

    /**
     * @test
     */
    public function paymentCanBeFetchedById()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $payment = $this->createPayment();
        $payment->setPaymentType($card);
        $authorization = $payment->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
        $this->assertEquals(12.0, $authorization->getAmount());

        $secPayment = $this->heidelpay->fetchPaymentById($payment->getId());
        $this->assertSame($payment->getId(), $secPayment->getId());
    }
}
