<?php
/**
 * This class defines unit tests to verify functionality of the Authorization transaction type.
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
namespace heidelpayPHP\test\unit\Resources\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;
use stdClass;

class ChargeTest extends BaseUnitTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     *
     * @throws Exception
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $charge = new Charge();
        $this->assertNull($charge->getAmount());
        $this->assertNull($charge->getCurrency());
        $this->assertNull($charge->getReturnUrl());
        $this->assertNull($charge->isCard3ds());

        $charge = new Charge(123.4, 'myCurrency', 'https://my-return-url.test');
        $charge->setCard3ds(true);
        $this->assertEquals(123.4, $charge->getAmount());
        $this->assertEquals('myCurrency', $charge->getCurrency());
        $this->assertEquals('https://my-return-url.test', $charge->getReturnUrl());
        $this->assertTrue($charge->isCard3ds());

        $charge->setAmount(567.8)->setCurrency('myNewCurrency')->setReturnUrl('https://another-return-url.test');
        $charge->setCard3ds(false);
        $this->assertEquals(567.8, $charge->getAmount());
        $this->assertEquals('myNewCurrency', $charge->getCurrency());
        $this->assertEquals('https://another-return-url.test', $charge->getReturnUrl());
        $this->assertFalse($charge->isCard3ds());
    }

    /**
     * Verify that a Charge can be updated on handle response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function aChargeShouldBeUpdatedThroughResponseHandling()
    {
        $charge = new Charge();
        $this->assertNull($charge->getAmount());
        $this->assertNull($charge->getCurrency());
        $this->assertNull($charge->getReturnUrl());
        $this->assertNull($charge->getIban());
        $this->assertNull($charge->getBic());
        $this->assertNull($charge->getHolder());
        $this->assertNull($charge->getDescriptor());

        $charge = new Charge(123.4, 'myCurrency', 'https://my-return-url.test');
        $this->assertEquals(123.4, $charge->getAmount());
        $this->assertEquals('myCurrency', $charge->getCurrency());
        $this->assertEquals('https://my-return-url.test', $charge->getReturnUrl());

        $testResponse = new stdClass();
        $testResponse->amount = '789.0';
        $testResponse->currency = 'TestCurrency';
        $testResponse->returnUrl = 'https://return-url.test';
        $testResponse->Iban = 'DE89370400440532013000';
        $testResponse->Bic = 'COBADEFFXXX';
        $testResponse->Holder = 'Merchant Khang';
        $testResponse->Descriptor = '4065.6865.6416';

        $charge->handleResponse($testResponse);
        $this->assertEquals(789.0, $charge->getAmount());
        $this->assertEquals('TestCurrency', $charge->getCurrency());
        $this->assertEquals('https://return-url.test', $charge->getReturnUrl());
        $this->assertEquals('DE89370400440532013000', $charge->getIban());
        $this->assertEquals('COBADEFFXXX', $charge->getBic());
        $this->assertEquals('Merchant Khang', $charge->getHolder());
        $this->assertEquals('4065.6865.6416', $charge->getDescriptor());
    }

    /**
     * Verify getLinkedResources throws exception if the paymentType is not set.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function getLinkedResourcesShouldThrowExceptionWhenThePaymentTypeIsNotSet()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Payment type is missing!');

        (new Charge())->getLinkedResources();
    }

    /**
     * Verify linked resource.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function getLinkedResourceShouldReturnResourcesBelongingToCharge()
    {
        $heidelpayObj    = new Heidelpay('s-priv-123345');
        $paymentType     = (new Sofort())->setId('123');
        $customer        = (new Customer('Max', 'Mustermann'))->setId('123');
        $payment         = new Payment();
        $payment->setParentResource($heidelpayObj)->setPaymentType($paymentType)->setCustomer($customer);

        $charge          = (new Charge())->setPayment($payment);
        $linkedResources = $charge->getLinkedResources();
        $this->assertArrayHasKey('customer', $linkedResources);
        $this->assertArrayHasKey('type', $linkedResources);

        $this->assertSame($paymentType, $linkedResources['type']);
        $this->assertSame($customer, $linkedResources['customer']);
    }

    /**
     * Verify cancel() calls cancelCharge() on heidelpay object with the given amount.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelShouldCallCancelChargeOnHeidelpayObject()
    {
        $charge =  new Charge();
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)
            ->disableOriginalConstructor()
            ->setMethods(['cancelCharge'])
            ->getMock();
        $heidelpayMock->expects($this->exactly(2))
            ->method('cancelCharge')->willReturn(new Cancellation())
            ->withConsecutive(
                [$this->identicalTo($charge), $this->isNull()],
                [$this->identicalTo($charge), 321.9]
            );

        /** @var Heidelpay $heidelpayMock */
        $charge->setParentResource($heidelpayMock);
        $charge->cancel();
        $charge->cancel(321.9);
    }
}
