<?php
/**
 * This class defines unit tests to verify functionality of the HeidelpayApiException.
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
namespace heidelpayPHP\test\unit\Exceptions;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class HeidelpayApiExceptionTest extends BaseUnitTest
{
    /**
     * Verify the exception stores the given data.
     *
     * @test
     *
     * @throws Exception
     */
    public function heidelpayApiExceptionShouldReturnDefaultDataWhenNoneIsSet()
    {
        $exception = new HeidelpayApiException();
        $this->assertEquals(HeidelpayApiException::CLIENT_MESSAGE, $exception->getClientMessage());
        $this->assertEquals(HeidelpayApiException::MESSAGE, $exception->getMerchantMessage());
        $this->assertEquals('No error code provided', $exception->getCode());
    }

    /**
     * Verify the exception stores the given data.
     *
     * @test
     * @dataProvider exceptionDataProvider
     *
     * @param array $expected
     * @param array $testData
     *
     * @throws Exception
     */
    public function heidelpayApiExceptionShouldReturnTheGivenData(array $testData, array $expected)
    {
        $exception = new HeidelpayApiException($testData['message'], $testData['client_message'], $testData['code']);
        $this->assertEquals($expected['message'], $exception->getMerchantMessage());
        $this->assertEquals($expected['client_message'], $exception->getClientMessage());
        $this->assertEquals($expected['code'], $exception->getCode());
    }

    //<editor-fold desc="DataProviders">

    /**
     * @return array
     */
    public function exceptionDataProvider(): array
    {
        return [
            'default' => [
                    'testData' => ['message' => null, 'client_message' => null, 'code' => null],
                    'expected' => [
                        'message' => HeidelpayApiException::MESSAGE,
                        'client_message' => HeidelpayApiException::CLIENT_MESSAGE,
                        'code' => ''
                    ]
                ],
            'message' => [
                    'testData' => ['message' => 'myMessage', 'client_message' => null, 'code' => null],
                    'expected' => [
                        'message' => 'myMessage',
                        'client_message' => HeidelpayApiException::CLIENT_MESSAGE,
                        'code' => ''
                    ]
                ],
            'clientMessage' => [
                    'testData' => ['message' => 'myMessage', 'client_message' => 'myClientMessage', 'code' => null],
                    'expected' => ['message' => 'myMessage', 'client_message' => 'myClientMessage', 'code' => null]
                ],
            'code' => [
                    'testData' => ['message' => 'myMessage', 'client_message' => 'myClientMessage', 'code' => 'myCode'],
                    'expected' => ['message' => 'myMessage', 'client_message' => 'myClientMessage', 'code' => 'myCode']
                ]
        ];
    }

    //</editor-fold>
}
