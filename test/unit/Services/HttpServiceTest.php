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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit;

use heidelpayPHP\Adapter\CurlAdapter;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\DebugHandlerInterface;
use heidelpayPHP\Services\EnvironmentService;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\test\BaseUnitTest;
use heidelpayPHP\test\unit\Services\DummyAdapter;
use heidelpayPHP\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;

class HttpServiceTest extends BaseUnitTest
{
    /**
     * Verify getAdapter will return a CurlAdapter if none has been set.
     *
     * @test
     *
     * @throws RuntimeException
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
     * @throws RuntimeException
     */
    public function getAdapterShouldReturnCustomAdapterIfItHasBeenSet()
    {
        $dummyAdapter = new DummyAdapter();
        $httpService = (new HttpService())->setHttpAdapter($dummyAdapter);
        $this->assertSame($dummyAdapter, $httpService->getAdapter());
    }

    /**
     * Verify an environment service can be injected.
     *
     * @test
     */
    public function environmentServiceShouldBeInjectable()
    {
        $envService = new EnvironmentService();
        $httpService = new HttpService();
        $this->assertNotSame($envService, $httpService->getEnvironmentService());
        $httpService->setEnvironmentService($envService);
        $this->assertSame($envService, $httpService->getEnvironmentService());
    }

    /**
     * Verify send will throw exception if resource is null.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function sendShouldThrowExceptionIfResourceIsNotSet()
    {
        $httpService = new HttpService();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transfer object is empty!');
        $httpService->send();
    }

    /**
     * Verify send calls methods to setup and send request.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function sendShouldInitAndSendRequest()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();

        $resource = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));
        $adapterMock->expects($this->once())->method('init')->with(
            $this->callback(
                static function ($url) {
                    return str_replace(['dev-api', 'stg-api'], 'api', $url) === 'https://api.heidelpay.com/v1/my/uri/123';
                }),
            '{"dummyResource": "JsonSerialized"}',
            'GET'
        );
        $adapterMock->expects($this->once())->method('setUserAgent')->with('HeidelpayPHP');
        $headers = [
            'Authorization' => 'Basic cy1wcml2LU15VGVzdEtleTo=',
            'Content-Type'  => 'application/json',
            'SDK-VERSION'   => Heidelpay::SDK_VERSION,
            'SDK-TYPE'   => Heidelpay::SDK_TYPE
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

    /**
     * Verify 'Accept-Language' header only set when a locale is defined in the heidelpay object.
     *
     * @test
     * @dataProvider languageShouldOnlyBeSetIfSpecificallyDefinedDP
     *
     * @param $locale
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function languageShouldOnlyBeSetIfSpecificallyDefined($locale)
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();
        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(['setHeaders', 'execute'])->getMock();
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey', $locale));

        $adapterMock->expects($this->once())->method('setHeaders')->with(
            $this->callback(
                static function ($headers) use ($locale) {
                    return $locale === ($headers['Accept-Language'] ?? null);
                })
        );
        $adapterMock->method('execute')->willReturn('myResponseString');

        /** @var HttpService $httpServiceMock*/
        $httpServiceMock->send('/my/uri/123', $resource);
    }

    /**
     * Verify debugLog logs to debug handler if debug mode and a handler are set.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function sendShouldLogDebugMessagesIfDebugModeAndHandlerAreSet()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('execute')->willReturn('{"response":"myResponseString"}');
        $adapterMock->method('getResponseCode')->willReturnOnConsecutiveCalls('200', '201');
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $loggerMock = $this->getMockBuilder(DummyDebugHandler::class)->setMethods(['log'])->getMock();
        $loggerMock->expects($this->exactly(5))->method('log')->withConsecutive(
            [ $this->callback(
                    static function ($string) {
                        return str_replace(['dev-api', 'stg-api'], 'api', $string) === 'GET: https://api.heidelpay.com/v1/my/uri/123';
                    })
            ],
            ['Response: (200) {"response":"myResponseString"}'],
            [ $this->callback(
                static function ($string) {
                    return str_replace(['dev-api', 'stg-api'], 'api', $string) === 'POST: https://api.heidelpay.com/v1/my/uri/123';
                })
            ],
            ['Request: {"dummyResource": "JsonSerialized"}'],
            ['Response: (201) {"response":"myResponseString"}']
        );

        /** @var DebugHandlerInterface $loggerMock */
        $heidelpay = (new Heidelpay('s-priv-MyTestKey'))->setDebugMode(true)->setDebugHandler($loggerMock);
        $resource  = (new DummyResource())->setParentResource($heidelpay);

        /** @var HttpService $httpServiceMock*/
        $response = $httpServiceMock->send('/my/uri/123', $resource);
        $this->assertEquals('{"response":"myResponseString"}', $response);

        $response = $httpServiceMock->send('/my/uri/123', $resource, HttpAdapterInterface::REQUEST_POST);
        $this->assertEquals('{"response":"myResponseString"}', $response);
    }

    /**
     * Verify handleErrors will throw Exception if response string is null.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseIsEmpty()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('execute')->willReturn(null);
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The Request returned a null response!');
        $this->expectExceptionCode('No error code provided');

        /** @var HttpService $httpServiceMock*/
        $httpServiceMock->send('/my/uri/123', $resource);
    }

    /**
     * Verify handleErrors will throw Exception if responseCode is greaterOrEqual to 400.
     *
     * @test
     * @dataProvider responseCodeProvider
     *
     * @param string $responseCode
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseCodeIsGoE400($responseCode)
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();

        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();
        $adapterMock->method('getResponseCode')->willReturn($responseCode);
        $adapterMock->method('execute')->willReturn('{"response" : "myResponseString"}');
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionMessage('The payment api returned an error!');
        $this->expectExceptionCode('No error code provided');

        /** @var HttpService $httpServiceMock*/
        $httpServiceMock->send('/my/uri/123', $resource);
    }

    /**
     * Verify handleErrors will throw Exception if response contains errors field.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function handleErrorsShouldThrowExceptionIfResponseContainsErrorField()
    {
        $httpServiceMock = $this->getMockBuilder(HttpService::class)->setMethods(['getAdapter'])->getMock();
        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(
            ['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close']
        )->getMock();

        $firstResponse = '{"errors": [{}]}';
        $secondResponse = '{"errors": [{"merchantMessage": "This is an error message for the merchant!"}]}';
        $thirdResponse = '{"errors": [{"customerMessage": "This is an error message for the customer!"}]}';
        $fourthResponse = '{"errors": [{"code": "This is the error code!"}]}';
        $fifthResponse = '{"errors": [{"code": "This is the error code!"}], "id": "s-err-1234"}';
        $sixthResponse = '{"errors": [{"code": "This is the error code!"}], "id": "s-rre-1234"}';

        $adapterMock->method('execute')->willReturnOnConsecutiveCalls($firstResponse, $secondResponse, $thirdResponse, $fourthResponse, $fifthResponse, $sixthResponse);
        $httpServiceMock->method('getAdapter')->willReturn($adapterMock);

        $resource  = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));

        /** @var HttpService $httpServiceMock*/
        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The first exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMerchantMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('No error code provided', $e->getCode());
            $this->assertEquals('No error id provided', $e->getErrorId());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The second exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('This is an error message for the merchant!', $e->getMerchantMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('No error code provided', $e->getCode());
            $this->assertEquals('No error id provided', $e->getErrorId());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The third exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMerchantMessage());
            $this->assertEquals('This is an error message for the customer!', $e->getClientMessage());
            $this->assertEquals('No error code provided', $e->getCode());
            $this->assertEquals('No error id provided', $e->getErrorId());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The fourth exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMerchantMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('This is the error code!', $e->getCode());
            $this->assertEquals('No error id provided', $e->getErrorId());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The fifth exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMerchantMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('This is the error code!', $e->getCode());
            $this->assertEquals('s-err-1234', $e->getErrorId());
        }

        try {
            $httpServiceMock->send('/my/uri/123', $resource);
            $this->assertTrue(false, 'The sixth exception should have been thrown!');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals('The payment api returned an error!', $e->getMerchantMessage());
            $this->assertEquals('The payment api returned an error!', $e->getClientMessage());
            $this->assertEquals('This is the error code!', $e->getCode());
            $this->assertEquals('No error id provided', $e->getErrorId());
        }
    }

    /**
     * Verify environment switches when environment variable defines mgw environment.
     *
     * @test
     *
     * @dataProvider environmentUrlSwitchesWithEnvironmentVariableDP
     *
     * @param $environment
     * @param $apiUrl
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws Exception
     */
    public function environmentUrlSwitchesWithEnvironmentVariable($environment, $apiUrl)
    {
        $adapterMock = $this->getMockBuilder(CurlAdapter::class)->setMethods(['init', 'setUserAgent', 'setHeaders', 'execute', 'getResponseCode', 'close'])->getMock();
        $adapterMock->expects($this->once())->method('init')->with($apiUrl, self::anything(), self::anything());
        $resource = (new DummyResource())->setParentResource(new Heidelpay('s-priv-MyTestKey'));
        $adapterMock->method('execute')->willReturn('myResponseString');
        $adapterMock->method('getResponseCode')->willReturn('myResponseCode');

        $envSrvMock = $this->getMockBuilder(EnvironmentService::class)->setMethods(['getMgwEnvironment'])->getMock();
        $envSrvMock->method('getMgwEnvironment')->willReturn($environment);

        /**
         * @var CurlAdapter        $adapterMock
         * @var EnvironmentService $envSrvMock
         */
        $httpService = (new HttpService())->setHttpAdapter($adapterMock)->setEnvironmentService($envSrvMock);

        /** @var HttpService $httpServiceMock*/
        $response = $httpService->send('', $resource);

        $this->assertEquals('myResponseString', $response);
    }

    //<editor-fold desc="DataProviders">

    /**
     * Data provider for handleErrorsShouldThrowExceptionIfResponseCodeIsGoE400.
     *
     * @return array
     */
    public function responseCodeProvider(): array
    {
        return [
            '400' => ['400'],
            '401' => ['401'],
            '404' => ['404'],
            '500' => ['500'],
            '600' => ['600'],
            '1000' => ['1000']
        ];
    }

    /**
     * Returns test data for method public function languageShouldOnlyBeSetIfSpecificallyDefined.
     */
    public function languageShouldOnlyBeSetIfSpecificallyDefinedDP(): array
    {
        return [
            'de-DE' => ['de-DE'],
            'en-US' => ['en-US'],
            'null' => [null]
        ];
    }

    /**
     * @return array
     */
    public function environmentUrlSwitchesWithEnvironmentVariableDP(): array
    {
        return [
            'Dev' => [EnvironmentService::ENV_VAR_VALUE_DEVELOPMENT_ENVIRONMENT, 'https://dev-api.heidelpay.com/v1'],
            'Prod' => [EnvironmentService::ENV_VAR_VALUE_PROD_ENVIRONMENT, 'https://api.heidelpay.com/v1'],
            'Stg' => [EnvironmentService::ENV_VAR_VALUE_STAGING_ENVIRONMENT, 'https://stg-api.heidelpay.com/v1'],
            'else' => ['something else', 'https://api.heidelpay.com/v1'],
            'undefined' => [false, 'https://api.heidelpay.com/v1']
        ];
    }

    //</editor-fold>
}
