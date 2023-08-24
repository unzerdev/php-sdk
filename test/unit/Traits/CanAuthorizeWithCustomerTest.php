<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the CanAuthorizeWithCustomer trait.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\unit
 */

namespace UnzerSDK\test\unit\Traits;

use UnzerSDK\Unzer;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\test\BasePaymentTest;
use RuntimeException;

class CanAuthorizeWithCustomerTest extends BasePaymentTest
{
    /**
     * Verify authorize method throws exception if the class does not implement the UnzerParentInterface.
     *
     * @test
     */
    public function authorizeShouldThrowExceptionIfTheClassDoesNotImplementParentInterface(): void
    {
        $dummy = new TraitDummyWithCustomerWithoutParentIF();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TraitDummyWithCustomerWithoutParentIF');

        $dummy->authorize(1.0, 'MyCurrency', 'https://return.url', new Customer());
    }

    /**
     * Verify authorize method propagates authorize method to Unzer object.
     *
     * @test
     */
    public function authorizeShouldPropagateAuthorizeToUnzer(): void
    {
        $unzerMock = $this->getMockBuilder(Unzer::class)->setMethods(['authorize'])->disableOriginalConstructor()->getMock();
        $dummyMock     = $this->getMockBuilder(TraitDummyWithCustomerWithParentIF::class)->setMethods(['getUnzerObject'])->getMock();

        $authorize = new Authorization();
        $customer  = (new Customer())->setId('123');
        $metadata  = new Metadata();
        $dummyMock->expects($this->exactly(3))->method('getUnzerObject')->willReturn($unzerMock);
        $unzerMock->expects($this->exactly(3))->method('authorize')
            ->withConsecutive(
                [1.2, 'MyCurrency2', $dummyMock, 'https://return.url2', $customer, null],
                [1.3, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId'],
                [1.4, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId', $metadata]
            )->willReturn($authorize);


        /** @var TraitDummyWithCustomerWithParentIF $dummyMock */
        $returnedAuthorize = $dummyMock->authorize(1.2, 'MyCurrency2', 'https://return.url2', $customer);
        $this->assertSame($authorize, $returnedAuthorize);
        $returnedAuthorize = $dummyMock->authorize(1.3, 'MyCurrency3', 'https://return.url3', $customer, 'orderId');
        $this->assertSame($authorize, $returnedAuthorize);
        $returnedAuthorize = $dummyMock->authorize(1.4, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', $metadata);
        $this->assertSame($authorize, $returnedAuthorize);
    }
}
