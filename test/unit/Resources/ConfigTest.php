<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Config resource.
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
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\test\unit
 */
namespace UnzerSDK\test\unit\Resources;

use UnzerSDK\Resources\Config;
use UnzerSDK\test\BasePaymentTest;

class ConfigTest extends BasePaymentTest
{
    /**
     * Verify the constructor of the webhook resource behaves as expected.
     *
     * @test
     */
    public function mandatoryConstructorParametersShouldDefaultToEmptyString(): void
    {
        $config = new Config();
        $this->assertEquals('', $config->getOptinText());
    }

    /**
     * Verify the getters and setters of the webhook resource.
     *
     * @test
     */
    public function gettersAndSettersOfWebhookShouldBehaveAsExpected(): void
    {
        $config = new Config();
        $this->assertEquals('', $config->getOptinText());

        $responseArray = ['optinText' => 'Some opt-in text.'];
        
        $config->handleResponse((object)$responseArray);
        $this->assertEquals('Some opt-in text.', $config->getOptinText());
    }
}
