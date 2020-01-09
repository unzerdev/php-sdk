<?php
/**
 * This class defines unit tests to verify functionality of the CanRecur trait.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
namespace heidelpayPHP\test\unit\Traits;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Recurring;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;
use stdClass;

class CanRecurTest extends BasePaymentTest
{
    /**
     * Verify setters and getters.
     *
     * @test
     *
     * @throws AssertionFailedError
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $dummy = new TraitDummyCanRecur();
        $this->assertFalse($dummy->isRecurring());
        $response = new stdClass();
        $response->recurring = true;
        $dummy->handleResponse($response);
        $this->assertTrue($dummy->isRecurring());
    }

    /**
     * Verify recurring activation on a resource which is not an abstract resource will throw an exception.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function activateRecurringWillThrowExceptionIfTheObjectHasWrongType()
    {
        $dummy = new TraitDummyCanRecurNonResource();

        $this->expectException(RuntimeException::class);
        $dummy->activateRecurring('1234');
    }

    /**
     * Verify activation on object will call heidelpay.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function activateRecurringWillCallHeidelpayMethod()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()->setMethods(['activateRecurringPayment'])->getMock();

        /** @var Heidelpay $heidelpayMock */
        $dummy = (new TraitDummyCanRecur())->setParentResource($heidelpayMock);
        $heidelpayMock->expects(self::once())->method('activateRecurringPayment')->with($dummy, 'return url')->willReturn(new Recurring('', ''));

        $dummy->activateRecurring('return url');
    }
}
