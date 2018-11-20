<?php
/**
 * This class defines unit tests to verify functionality of the Payment resource.
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
namespace heidelpay\MgwPhpSdk\test\unit\Resources;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\EmbeddedResources\Amount;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $payment = (new Payment())->setParentResource(new Heidelpay('s-priv-1234'));
        $this->assertNull($payment->getRedirectUrl());
        $this->assertNull($payment->getCustomer());
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Amount::class, $payment->getAmount());

        $payment->setRedirectUrl('https://my-redirect-url.test');
        $this->assertEquals('https://my-redirect-url.test', $payment->getRedirectUrl());

        $authorize = new Authorization();
        $payment->setAuthorization($authorize);
        $this->assertSame($authorize, $payment->getAuthorization(true));
    }

    /**
     * Verify getAuthorization should try to fetch resource if lazy loading is off and the authorization is not null.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function getAuthorizationShouldFetchAuthorizeIfNotLazyAndAuthIsNotNull()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $authorization = (new Authorization())->setParentResource($payment);
        $payment->setAuthorization($authorization);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('getResource')->with($authorization);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->getAuthorization();
    }

    //<editor-fold desc="Helpers">

    /**
     * This performs assertions to verify the tested value is an empty array.
     *
     * @param mixed $value
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function assertIsEmptyArray($value)
    {
        $this->assertInternalType('array', $value);
        $this->assertEmpty($value);
    }

    //</editor-fold>
}
