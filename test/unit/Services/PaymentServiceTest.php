<?php
/**
 * This class defines unit tests to verify functionality of the resource name service.
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
namespace heidelpay\MgwPhpSdk\test\unit\Services;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Services\PaymentService;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * Verify setters and getters work properly.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $paymentService = new PaymentService($heidelpay);
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertSame($heidelpay->getResourceService(), $paymentService->getResourceService());

        $heidelpay2 = new Heidelpay('s-priv-1234');
        $resourceService2 = new ResourceService($heidelpay2);
        $paymentService->setResourceService($resourceService2);
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $paymentService->getResourceService());
        $this->assertSame($resourceService2, $paymentService->getResourceService());

        $paymentService->setHeidelpay($heidelpay2);
        $this->assertSame($heidelpay2, $paymentService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $paymentService->getResourceService());
    }

    /**
     * Verify authorize method calls authorize with payment.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldCreatePaymentAndCallAuthorizeWithPayment()
    {
        $paymentType = (new Sofort())->setId('typeId');
        $customer = (new Customer())->setId('customerId');

        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->disableOriginalConstructor()
            ->setMethods(['authorizeWithPayment'])->getMock();
        $paymentSrvMock->expects($this->exactly(3))->method('authorizeWithPayment')
            ->withConsecutive(
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url'],
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url', $customer],
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url', $customer, 'OrderId']
            );

        /** @var PaymentService $paymentSrvMock */
        $paymentSrvMock->setHeidelpay(new Heidelpay('s-priv-123'));
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url');
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url', $customer);
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url', $customer, 'OrderId');
    }
}
