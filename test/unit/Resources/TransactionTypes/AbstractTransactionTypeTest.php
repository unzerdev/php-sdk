<?php
/**
 * This class defines unit tests to verify functionality of the AbstractTransactionType.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class AbstractTransactionTypeTest extends TestCase
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function theGettersAndSettersShouldWorkProperly()
    {
        $payment = new Payment();
        $transactionType = new DummyTransactionType();
        $this->assertNull($transactionType->getPayment());
        $this->assertNull($transactionType->getDate());
        $this->assertNull($transactionType->getPaymentId());

        $transactionType->setPayment($payment);
        $this->assertNull($transactionType->getRedirectUrl());

        $payment->setId('MyPaymentId');
        $date = (new \DateTime('now'))->format('Y-m-d h:i:s');
        $transactionType->setPayment($payment);
        $transactionType->setDate($date);

        $this->assertSame($payment, $transactionType->getPayment());
        $this->assertEquals($date, $transactionType->getDate());
        $this->assertNull($transactionType->getExternalId());
        $this->assertEquals($payment->getId(), $transactionType->getPaymentId());
    }

    /**
     * Verify getRedirectUrl() calls Payment::getRedirectUrl().
     *
     * @test
     *
     * @throws Exception
     * @throws \ReflectionException
     * @throws RuntimeException
     */
    public function getRedirectUrlShouldCallPaymentGetRedirectUrl()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getRedirectUrl'])->getMock();
        $paymentMock->expects($this->once())->method('getRedirectUrl')->willReturn('https://my-redirect-url.test');

        $transactionType = new DummyTransactionType();

        /** @var Payment $paymentMock */
        $transactionType->setPayment($paymentMock);
        $this->assertEquals('https://my-redirect-url.test', $transactionType->getRedirectUrl());
    }

    /**
     * Verify abstract transaction allows for updating.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateValuesOfAbstractTransaction()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $transactionType = (new DummyTransactionType())->setPayment($payment);
        $this->assertNull($transactionType->getUniqueId());
        $this->assertNull($transactionType->getShortId());
        $this->assertNull($transactionType->getRedirectUrl());
        $this->assertEquals('myPaymentId', $transactionType->getPaymentId());

        $testResponse = new \stdClass();
        $testResponse->uniqueId = 'myUniqueId';
        $testResponse->shortId = 'myShortId';
        $testResponse->redirectUrl = 'myRedirectUrl';
        $testResources = new \stdClass();
        $testResources->paymentId = 'myNewPaymentId';
        $testResponse->resources = $testResources;
        $transactionType->handleResponse($testResponse);

        $this->assertEquals('myUniqueId', $transactionType->getUniqueId());
        $this->assertEquals('myShortId', $transactionType->getShortId());
        $this->assertEquals('myRedirectUrl', $payment->getRedirectUrl());
        $this->assertEquals('myNewPaymentId', $payment->getId());
    }

    /**
     * Verify fetchPayment is never called after a Get-Request.
     *
     * @test
     * @dataProvider updatePaymentDataProvider
     *
     * @param string  $method
     * @param integer $timesCalled
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function updatePaymentShouldOnlyBeCalledOnNotRequests($method, $timesCalled)
    {
        $transactionTypeMock =
            $this->getMockBuilder(DummyTransactionType::class)->setMethods(['updatePayment'])->getMock();
        $transactionTypeMock->expects($this->exactly($timesCalled))->method('updatePayment');

        /** @var AbstractTransactionType $transactionTypeMock */
        $transactionTypeMock->handleResponse(new \stdClass(), $method);
    }

    //<editor-fold desc="Data Providers">

    /**
     * DataProvider for updatePaymentShouldOnlyBeCalledOnGetRequests.
     *
     * @return array
     */
    public function updatePaymentDataProvider(): array
    {
        return [
            HttpAdapterInterface::REQUEST_GET => [HttpAdapterInterface::REQUEST_GET, 0],
            HttpAdapterInterface::REQUEST_POST => [HttpAdapterInterface::REQUEST_POST, 1],
            HttpAdapterInterface::REQUEST_PUT => [HttpAdapterInterface::REQUEST_PUT, 1],
            HttpAdapterInterface::REQUEST_DELETE => [HttpAdapterInterface::REQUEST_DELETE, 1],
        ];
    }

    //</editor-fold>
}
