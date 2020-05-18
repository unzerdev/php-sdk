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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\unit
 */
namespace heidelpayPHP\test\unit\Resources\TransactionTypes;

use DateTime;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use RuntimeException;
use stdClass;

class AbstractTransactionTypeTest extends BasePaymentTest
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws \Exception
     */
    public function theGettersAndSettersShouldWorkProperly()
    {
        // initial check
        $payment = new Payment();
        $transactionType = new DummyTransactionType();
        $this->assertNull($transactionType->getPayment());
        $this->assertNull($transactionType->getDate());
        $this->assertNull($transactionType->getPaymentId());
        $this->assertNull($transactionType->getShortId());
        $this->assertNull($transactionType->getUniqueId());
        $this->assertNull($transactionType->getTraceId());

        $this->assertFalse($transactionType->isError());
        $this->assertFalse($transactionType->isSuccess());
        $this->assertFalse($transactionType->isPending());

        $message = $transactionType->getMessage();
        $this->assertEmpty($message->getCode());
        $this->assertEmpty($message->getCustomer());

        $transactionType->setPayment($payment);
        $this->assertNull($transactionType->getRedirectUrl());

        // update
        $payment->setId('MyPaymentId');
        $date = (new DateTime('now'))->format('Y-m-d H:i:s');
        $transactionType->setDate($date);
        $ids = (object)['shortId' => 'myShortId', 'uniqueId' => 'myUniqueId', 'traceId' => 'myTraceId'];
        $transactionType->handleResponse((object)['isError' => true, 'isPending' => true, 'isSuccess' => true, 'processing' => $ids]);
        $messageResponse = (object)['code' => '1234', 'customer' => 'Customer message!'];
        $transactionType->handleResponse((object)['message' => $messageResponse]);

        // check again
        $this->assertSame($payment, $transactionType->getPayment());
        $this->assertSame($date, $transactionType->getDate());
        $this->assertNull($transactionType->getExternalId());
        $this->assertSame($payment->getId(), $transactionType->getPaymentId());
        $this->assertTrue($transactionType->isSuccess());
        $this->assertTrue($transactionType->isPending());
        $this->assertTrue($transactionType->isError());
        $this->assertSame('myShortId', $transactionType->getShortId());
        $this->assertSame('myUniqueId', $transactionType->getUniqueId());
        $this->assertSame('myTraceId', $transactionType->getTraceId());

        $message = $transactionType->getMessage();
        $this->assertSame('1234', $message->getCode());
        $this->assertSame('Customer message!', $message->getCustomer());
    }

    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws \Exception
     *
     * Todo: Workaround to be removed when API sends TraceID in processing-group
     */
    public function checkTraceIdWorkaround()
    {
        // initial check
        $transactionType = new DummyTransactionType();
        $this->assertNull($transactionType->getTraceId());

        // update
        $transactionType->handleResponse((object)['resources' => (object)['traceId' => 'myTraceId']]);

        // check again
        $this->assertSame('myTraceId', $transactionType->getTraceId());
    }

    /**
     * Verify getRedirectUrl() calls Payment::getRedirectUrl().
     *
     * @test
     *
     * @throws Exception
     * @throws ReflectionException
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function handleResponseShouldUpdateValuesOfAbstractTransaction()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $transactionType = (new DummyTransactionType())->setPayment($payment);
        $this->assertNull($transactionType->getUniqueId());
        $this->assertNull($transactionType->getShortId());
        $this->assertNull($transactionType->getRedirectUrl());
        $this->assertEquals('myPaymentId', $transactionType->getPaymentId());

        $testResponse = new stdClass();
        $testResponse->uniqueId = 'myUniqueId';
        $testResponse->shortId = 'myShortId';
        $testResponse->redirectUrl = 'myRedirectUrl';
        $testResources = new stdClass();
        $testResources->paymentId = 'myNewPaymentId';
        $testResponse->resources = $testResources;
        $message = new stdClass();
        $message->code = 'myCode';
        $message->customer = 'Customer message';
        $testResponse->message = $message;
        $transactionType->handleResponse($testResponse);

        $this->assertEquals('myUniqueId', $transactionType->getUniqueId());
        $this->assertEquals('myShortId', $transactionType->getShortId());
        $this->assertEquals('myCode', $transactionType->getMessage()->getCode());
        $this->assertEquals('Customer message', $transactionType->getMessage()->getCustomer());
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
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updatePaymentShouldOnlyBeCalledOnNotRequests($method, $timesCalled)
    {
        $transactionTypeMock =
            $this->getMockBuilder(DummyTransactionType::class)->setMethods(['fetchPayment'])->getMock();
        $transactionTypeMock->expects($this->exactly($timesCalled))->method('fetchPayment');

        /** @var AbstractTransactionType $transactionTypeMock */
        $transactionTypeMock->handleResponse(new stdClass(), $method);
    }

    /**
     * Verify payment object is fetched on fetchPayment call using the heidelpays resource service object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function fetchPaymentShouldFetchPaymentObject()
    {
        $payment = (new Payment())->setId('myPaymentId');

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchResource')->with($payment);

        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        /** @var DummyTransactionType $transactionType */
        $transactionType = (new DummyTransactionType())->setPayment($payment);
        $transactionType->fetchPayment();
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
            HttpAdapterInterface::REQUEST_DELETE => [HttpAdapterInterface::REQUEST_DELETE, 1]
        ];
    }

    //</editor-fold>
}
