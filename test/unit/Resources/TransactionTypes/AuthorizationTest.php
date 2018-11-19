<?php
/**
 * This class defines unit tests to verify functionality of the Authorization transaction type.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    /**
     * Verify getters and setters.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $authorization = new Authorization();
        $this->assertNull($authorization->getAmount());
        $this->assertNull($authorization->getCurrency());
        $this->assertNull($authorization->getReturnUrl());

        $authorization = new Authorization(123.4, 'myCurrency', 'https://my-return-url.test');
        $this->assertEquals(123.4, $authorization->getAmount());
        $this->assertEquals('myCurrency', $authorization->getCurrency());
        $this->assertEquals('https://my-return-url.test', $authorization->getReturnUrl());

        $authorization->setAmount(567.8)->setCurrency('myNewCurrency')->setReturnUrl('https://another-return-url.test');
        $this->assertEquals(567.8, $authorization->getAmount());
        $this->assertEquals('myNewCurrency', $authorization->getCurrency());
        $this->assertEquals('https://another-return-url.test', $authorization->getReturnUrl());
    }
}
