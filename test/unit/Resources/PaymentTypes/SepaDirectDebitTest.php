<?php
/**
 * This class defines unit tests to verify functionality of SepaDirectDebit payment type.
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
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class SepaDirectDebitTest extends BaseUnitTest
{
    /**
     * Verify constructor sets iban.
     *
     * @test
     *
     */
    public function ibanShouldBeSetByConstructor()
    {
        $sdd = new SepaDirectDebit(null);
        $this->assertNull($sdd->getIban());
    }

    /**
     * Verify setter and getter work.
     *
     * @test
     *
     * @throws Exception
     */
    public function getterAndSetterWorkAsExpected()
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');
        $this->assertEquals('DE89370400440532013000', $sdd->getIban());

        $sdd->setIban('DE89370400440532013012');
        $this->assertEquals('DE89370400440532013012', $sdd->getIban());

        $this->assertNull($sdd->getBic());
        $sdd->setBic('RABONL2U');
        $this->assertEquals('RABONL2U', $sdd->getBic());

        $this->assertNull($sdd->getHolder());
        $sdd->setHolder('Max Mustermann');
        $this->assertEquals('Max Mustermann', $sdd->getHolder());
    }
}
