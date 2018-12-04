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
use heidelpay\MgwPhpSdk\Services\HttpService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyAdapter;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class HttpServiceTest extends BaseUnitTest
{
    /**
     * Verify getAdapter will return a CurlAdapter if none has been set.
     *
     * @test
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
}
