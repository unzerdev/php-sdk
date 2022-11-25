<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the HasCancellations trait.
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

use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\test\BasePaymentTest;

class HasCancellationsTest extends BasePaymentTest
{
    /**
     * Verify getters setters.
     *
     * @test
     */
    public function hasCancellationGettersAndSettersShouldWorkProperly(): void
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
        $this->assertEquals([$cancellation1, $cancellation2, $cancellation3], $dummy->getCancellations());

        // assert getCancellation
        $this->assertSame($cancellation3, $dummy->getCancellation('3', true));

        // assert setCancellations
        $cancellation4 = (new Cancellation())->setId('4');
        $cancellation5 = (new Cancellation())->setId('5');
        $cancellation6 = (new Cancellation())->setId('6');
        $dummy->setCancellations([$cancellation4, $cancellation5, $cancellation6]);
        $this->assertEquals([$cancellation4, $cancellation5, $cancellation6], $dummy->getCancellations());
    }

    /**
     * Verify getCancellation will call getResource with the selected Cancellation if it is not lazy loaded.
     *
     * @test
     */
    public function getCancellationShouldCallGetResourceIfItIsNotLazyLoaded(): void
    {
        $cancel = (new Cancellation())->setId('myCancelId');
        $authorizeMock = $this->getMockBuilder(Authorization::class)->setMethods(['getResource'])->getMock();
        /** @noinspection PhpParamsInspection */
        $authorizeMock->expects($this->once())->method('getResource')->with($cancel);

        /** @var Authorization $authorizeMock */
        $authorizeMock->addCancellation($cancel);
        $this->assertSame($authorizeMock, $cancel->getParentResource());
        $this->assertSame($cancel, $authorizeMock->getCancellation('myCancelId'));
    }
}
