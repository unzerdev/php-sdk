<?php
/**
 * This class defines unit tests to verify functionality of the CanAuthorize trait.
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
namespace heidelpayPHP\test\unit\Traits;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\test\BaseUnitTest;
use ReflectionException;
use RuntimeException;

class CanAuthorizeTest extends BaseUnitTest
{
    /**
     * Verify authorize method throws exception if the class does not implement the HeidelpayParentInterface.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldThrowExceptionIfTheClassDoesNotImplementParentInterface()
    {
        $dummy = new TraitDummyWithoutCustomerWithoutParentIF();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TraitDummyWithoutCustomerWithoutParentIF');

        $dummy->authorize(1.0, 'MyCurrency', 'https://return.url');
    }

    /**
     * Verify authorize method propagates authorize method to heidelpay object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldPropagateAuthorizeToHeidelpay()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->setMethods(['authorize'])->disableOriginalConstructor()->getMock();
        $dummyMock     = $this->getMockBuilder(TraitDummyWithoutCustomerWithParentIF::class)->setMethods(['getHeidelpayObject'])->getMock();

        $authorize = new Authorization();
        $customer  = (new Customer())->setId('123');
        $metadata  = new Metadata();
        $dummyMock->expects($this->exactly(4))->method('getHeidelpayObject')->willReturn($heidelpayMock);
        $heidelpayMock->expects($this->exactly(4))->method('authorize')
            ->withConsecutive(
                [1.1, 'MyCurrency', $dummyMock, 'https://return.url', null, null],
                [1.2, 'MyCurrency2', $dummyMock, 'https://return.url2', $customer, null],
                [1.3, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId'],
                [1.4, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId', $metadata]
            )->willReturn($authorize);


        /** @var TraitDummyWithoutCustomerWithParentIF $dummyMock */
        $returnedAuthorize = $dummyMock->authorize(1.1, 'MyCurrency', 'https://return.url');
        $this->assertSame($authorize, $returnedAuthorize);
        $returnedAuthorize = $dummyMock->authorize(1.2, 'MyCurrency2', 'https://return.url2', $customer);
        $this->assertSame($authorize, $returnedAuthorize);
        $returnedAuthorize = $dummyMock->authorize(1.3, 'MyCurrency3', 'https://return.url3', $customer, 'orderId');
        $this->assertSame($authorize, $returnedAuthorize);
        $returnedAuthorize = $dummyMock->authorize(1.4, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', $metadata);
        $this->assertSame($authorize, $returnedAuthorize);
    }
}
