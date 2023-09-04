<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of Paylater Installment payment type.
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

namespace UnzerSDK\test\unit\Resources\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\test\BasePaymentTest;

class PaylaterInstallmentTest extends BasePaymentTest
{
    /**
     * Verify getters and setters work as expected.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected(): void
    {
        $pit = new PaylaterInstallment();
        $this->assertNull($pit->getInquiryId());
        $this->assertNull($pit->getNumberOfRates());
        $this->assertNull($pit->getIban());
        $this->assertNull($pit->getCountry());
        $this->assertNull($pit->getHolder());

        $pit->setInquiryId('inquiryId');
        $pit->setNumberOfRates(7);
        $pit->setIban('DE89370400440532013000');
        $pit->setCountry('DE');
        $pit->setHolder('Max Mustermann');

        $this->assertEquals('inquiryId', $pit->getInquiryId());
        $this->assertEquals(7, $pit->getNumberOfRates());
        $this->assertEquals('DE89370400440532013000', $pit->getIban());
        $this->assertEquals('DE', $pit->getCountry());
        $this->assertEquals('Max Mustermann', $pit->getHolder());

        $pit->setInquiryId(null);
        $pit->setNumberOfRates(null);
        $pit->setIban(null);
        $pit->setCountry(null);
        $pit->setHolder(null);

        $this->assertNull($pit->getInquiryId());
        $this->assertNull($pit->getNumberOfRates());
        $this->assertNull($pit->getIban());
        $this->assertNull($pit->getCountry());
        $this->assertNull($pit->getHolder());
    }
}
