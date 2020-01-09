<?php
/**
 * This class defines unit tests to verify functionality of the CanPayout trait.
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
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\test\BasePaymentTest;
use ReflectionException;
use RuntimeException;

class CanPayoutTest extends BasePaymentTest
{
    /**
     * Verify payout method throws exception if the class does not implement the HeidelpayParentInterface.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function payoutShouldThrowExceptionIfTheClassDoesNotImplementParentInterface()
    {
        $dummy = new TraitDummyWithoutCustomerWithoutParentIF();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TraitDummyWithoutCustomerWithoutParentIF');

        $dummy->payout(1.0, 'MyCurrency', 'https://return.url');
    }

    /**
     * Verify payout method propagates payout method to heidelpay object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function payoutShouldPropagatePayoutToHeidelpay()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->setMethods(['payout'])->disableOriginalConstructor()->getMock();
        $dummyMock     = $this->getMockBuilder(TraitDummyWithoutCustomerWithParentIF::class)->setMethods(['getHeidelpayObject'])->getMock();

        $payout = new Payout();
        $customer  = (new Customer())->setId('123');
        $metadata  = new Metadata();
        $dummyMock->expects($this->exactly(4))->method('getHeidelpayObject')->willReturn($heidelpayMock);
        $heidelpayMock->expects($this->exactly(4))->method('payout')
            ->withConsecutive(
                [1.1, 'MyCurrency', $dummyMock, 'https://return.url', null, null],
                [1.2, 'MyCurrency2', $dummyMock, 'https://return.url2', $customer, null],
                [1.3, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId'],
                [1.4, 'MyCurrency3', $dummyMock, 'https://return.url3', $customer, 'orderId', $metadata]
            )->willReturn($payout);


        /** @var TraitDummyWithoutCustomerWithParentIF $dummyMock */
        $returnedPayout = $dummyMock->payout(1.1, 'MyCurrency', 'https://return.url');
        $this->assertSame($payout, $returnedPayout);
        $returnedPayout = $dummyMock->payout(1.2, 'MyCurrency2', 'https://return.url2', $customer);
        $this->assertSame($payout, $returnedPayout);
        $returnedPayout = $dummyMock->payout(1.3, 'MyCurrency3', 'https://return.url3', $customer, 'orderId');
        $this->assertSame($payout, $returnedPayout);
        $returnedPayout = $dummyMock->payout(1.4, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', $metadata);
        $this->assertSame($payout, $returnedPayout);
    }
}
