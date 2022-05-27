<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the CanRecur trait.
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
use UnzerSDK\Resources\Recurring;
use UnzerSDK\test\BasePaymentTest;
use RuntimeException;
use stdClass;

class CanRecurTest extends BasePaymentTest
{
    /**
     * Verify setters and getters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): void
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
     */
    public function activateRecurringWillThrowExceptionIfTheObjectHasWrongType(): void
    {
        $dummy = new TraitDummyCanRecurNonResource();

        $this->expectException(RuntimeException::class);
        $dummy->activateRecurring('1234');
    }

    /**
     * Verify activation on object will call Unzer.
     *
     * @test
     */
    public function activateRecurringWillCallUnzerMethod(): void
    {
        $unzerMock = $this->getMockBuilder(Unzer::class)->disableOriginalConstructor()->setMethods(['activateRecurringPayment'])->getMock();

        /** @var Unzer $unzerMock */
        $dummy = (new TraitDummyCanRecur())->setParentResource($unzerMock);
        /** @noinspection PhpParamsInspection */
        $unzerMock->expects(self::once())->method('activateRecurringPayment')->with($dummy, 'return url')->willReturn(new Recurring('', ''));

        $dummy->activateRecurring('return url');
    }
}
