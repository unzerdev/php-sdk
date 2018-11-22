<?php
/**
 * This class defines unit tests to verify functionality of the resource service.
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

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class ResourceServiceTest extends TestCase
{
    /**
     * Verify send method will get the uri from the given resource.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function sendWillGetTheUriFromTheGivenResourceSendItViaHeidelpayObjectAndReturnAJsonDecodedResponse()
    {
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['getUri'])->getMock();
        $testResource->expects($this->once())->method('getUri')->willReturn('myUri');

        $heidelpay = $this->getMockBuilder(Heidelpay::class)->setMethods(['send'])->disableOriginalConstructor()
            ->getMock();
        $heidelpay->expects($this->once())->method('send')
            ->with('myUri', $testResource, HttpAdapterInterface::REQUEST_GET)
            ->willReturn('{"myTestKey": "myTestValue", "myTestKey2": {"myTestKey3": "myTestValue2"}}');

        /**
         * @var Heidelpay                 $heidelpay
         * @var AbstractHeidelpayResource $testResource
         */
        $testResource->setParentResource($heidelpay);
        $resourceService = new ResourceService($heidelpay);

        /** @var AbstractHeidelpayResource $testResource */
        $response = $resourceService->send($testResource);

        $expectedResponse = new \stdClass();
        $expectedResponse->myTestKey = 'myTestValue';
        $expectedResponse->myTestKey2 = new \stdClass();
        $expectedResponse->myTestKey2->myTestKey3 = 'myTestValue2';
        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Verify send method will call getUri with appendId depending on Http method.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function sendShouldCallGetUriWithAppendIdDependingOnHttpMethod()
    {
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['getUri'])->getMock();
        $testResource->expects($this->exactly(5))->method('getUri')->withConsecutive(
            [true],
            [true],
            [false],
            [true],
            [true]
        );

        $heidelpay = $this->getMockBuilder(Heidelpay::class)->setMethods(['send'])->disableOriginalConstructor()
            ->getMock();
        $heidelpay->method('send')->withAnyParameters()->willReturn('{}');

        /**
         * @var Heidelpay                 $heidelpay
         * @var AbstractHeidelpayResource $testResource
         */
        $testResource->setParentResource($heidelpay);
        $resourceService = new ResourceService($heidelpay);

        /** @var AbstractHeidelpayResource $testResource */
        $resourceService->send($testResource);
        $resourceService->send($testResource, HttpAdapterInterface::REQUEST_GET);
        $resourceService->send($testResource, HttpAdapterInterface::REQUEST_POST);
        $resourceService->send($testResource, HttpAdapterInterface::REQUEST_PUT);
        $resourceService->send($testResource, HttpAdapterInterface::REQUEST_DELETE);
    }
}
