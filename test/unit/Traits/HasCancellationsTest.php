<?php
/**
 * This class defines unit tests to verify functionality of the HasCancellations trait.
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
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\test\BaseUnitTest;
use ReflectionException;
use RuntimeException;

class HasCancellationsTest extends BaseUnitTest
{
    /**
     * Verify getters setters.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function hasCancellationGettersAndSettersShouldWorkProperly()
    {
        $dummy = new TraitDummyHasCancellationsHasPaymentState();
        $this->assertIsEmptyArray($dummy->getCancellations());

        // assert getCancellation
        $this->assertNull($dummy->getCancellation('3'));

        // assert addCancellation
        $cancellation1 = (new Cancellation())->setId('1');
        $cancellation2 = (new Cancellation())->setId('2');
        $cancellation3 = (new Cancellation())->setId('3');
        $dummy->addCancellation($cancellation1);
        $dummy->addCancellation($cancellation2);
        $dummy->addCancellation($cancellation3);
        $this->assertArraySubset([$cancellation1, $cancellation2, $cancellation3], $dummy->getCancellations());

        // assert getCancellation
        $this->assertSame($cancellation3, $dummy->getCancellation('3'));

        // assert setCancellations
        $cancellation4 = (new Cancellation())->setId('4');
        $cancellation5 = (new Cancellation())->setId('5');
        $cancellation6 = (new Cancellation())->setId('6');
        $dummy->setCancellations([$cancellation4, $cancellation5, $cancellation6]);
        $this->assertArraySubset([$cancellation4, $cancellation5, $cancellation6], $dummy->getCancellations());
    }

    /**
     * Verify getCancellation will call getResource with the selected Cancellation if it is not lazy loaded.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function getCancellationShouldCallGetResourceIfItIsNotLazyLoaded()
    {
        $cancel = (new Cancellation())->setId('myCancelId');
        $authorizeMock = $this->getMockBuilder(Authorization::class)->setMethods(['getResource'])->getMock();
        $authorizeMock->expects($this->once())->method('getResource')->with($cancel);

        /** @var Authorization $authorizeMock */
        $authorizeMock->addCancellation($cancel);
        $this->assertSame($authorizeMock, $cancel->getParentResource());
        $this->assertSame($cancel, $authorizeMock->getCancellation('myCancelId'));
    }
}
