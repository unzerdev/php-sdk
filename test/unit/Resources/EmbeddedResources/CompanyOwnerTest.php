<?php
/*
 *  Test company owner class for B2B customer.
 *
 *  Copyright (C) 2022 - today Unzer E-Com GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @link  https://docs.unzer.com/
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\test\unit\Resources\EmbeddedResources;

use UnzerSDK\Resources\EmbeddedResources\CompanyOwner;
use UnzerSDK\test\BasePaymentTest;

class CompanyOwnerTest extends BasePaymentTest
{
    /**
     * Verify setter and getter functionality.
     *
     * @test
     */
    public function settersAndGettersShouldWork(): void
    {
        $owner = new CompanyOwner();
        $this->assertNull($owner->getFirstname());
        $this->assertNull($owner->getLastname());
        $this->assertNull($owner->getBirthdate());

        $owner->setFirstname('firstname')
            ->setLastname('lastname')
            ->setBirthdate('01.01.1999');

        $this->assertEquals('firstname', $owner->getFirstname());
        $this->assertEquals('lastname', $owner->getLastname());
        $this->assertEquals('01.01.1999', $owner->getBirthdate());
    }
}
