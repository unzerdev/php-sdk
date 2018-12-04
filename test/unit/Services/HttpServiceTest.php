<?php
/**
 * This class defines unit tests to verify functionality of the HttpService.
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
namespace heidelpay\MgwPhpSdk\test\unit;

use heidelpay\MgwPhpSdk\Adapter\CurlAdapter;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Services\HttpService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyAdapter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

class HttpServiceTest extends BaseUnitTest
{
    /**
     * Verify getAdapter will return a CurlAdapter if none has been set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function getAdapterShouldReturnDefaultAdapterIfNonHasBeenSet()
    {
        $httpService = new HttpService();
        $this->assertInstanceOf(CurlAdapter::class, $httpService->getAdapter());
    }

    /**
     * Verify getAdapter will return custom adapter if it has been set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function getAdapterShouldReturnCustomAdapterIfItHasBeenSet()
    {
        $dummyAdapter = new DummyAdapter();
        $httpService = (new HttpService())->setHttpAdapter($dummyAdapter);
        $this->assertSame($dummyAdapter, $httpService->getAdapter());
    }

    /**
     * Verify send will throw exception if resource is null.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function sendShouldThrowExceptionIfResourceIsNotSet()
    {
        $httpService = new HttpService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transfer object is empty!');
        $httpService->send();
    }

    /**
     * Verify send calls methods to setup and send request as well as handling the response.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function sendShouldSendRequestAndHandleResponse()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();

        $resource = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));
        $adapterMock->expects($this->once())->method('init')->willReturn(
            'https://api.heidelpay.com/v1/my/uri/123',
            'dummyResourceJsonSerialized',
            'GET'
        );
        $adapterMock->expects($this->once())->method('setUserAgent')->with('HeidelpayPHP');
        $headers = [
            'Authorization' => 'Basic cy1wcml2LU15VGVzdEtleTo=',
            'Content-Type'  => 'application/json',
            'SDK-VERSION'   => '1.0.0.0-beta.3'
        ];
        $adapterMock->expects($this->once())->method('setHeaders')->with($headers);
        $adapterMock->expects($this->once())->method('execute')->willReturn('myResponseString');
        $adapterMock->expects($this->once())->method('getResponseCode')->willReturn('myResponseCode');
        $adapterMock->expects($this->once())->method('close');

        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        /** @var HttpService $httpServiceMock*/
        $response = $httpServiceMock->send('/my/uri/123', $resource);

        $this->assertEquals('myResponseString', $response);
    }
}
