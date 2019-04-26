<?php
/**
 * This class defines unit tests to verify functionality of the Cancellation transaction type.
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
namespace heidelpayPHP\test\unit\Resources\TransactionTypes;

use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class CancellationTest extends BaseUnitTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     *
     * @throws Exception
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $cancellation = new Cancellation();
        $this->assertNull($cancellation->getAmount());
        $this->assertEmpty($cancellation->getReasonCode());

        $cancellation = new Cancellation(123.4);
        $this->assertEquals(123.4, $cancellation->getAmount());
        $this->assertEmpty($cancellation->getReasonCode());

        $cancellation->setAmount(567.8);
        $this->assertEquals(567.8, $cancellation->getAmount());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_CANCEL);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_CANCEL, $cancellation->getReasonCode());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_CREDIT);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_CREDIT, $cancellation->getReasonCode());

        $cancellation->setReasonCode(CancelReasonCodes::REASON_CODE_RETURN);
        $this->assertEquals(CancelReasonCodes::REASON_CODE_RETURN, $cancellation->getReasonCode());

        $cancellation->setReasonCode(null);
        $this->assertNull($cancellation->getReasonCode());
    }
}
