<?php
/**
 * This class defines unit tests to verify functionality of the Webhook resource.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class WebhookTest extends BaseUnitTest
{
    /**
     * Verify the constructor of the webhook resource behaves as expected.
     *
     * @test
     *
     * @throws Exception
     */
    public function mandatoryConstructorParametersShouldDefaultToEmptyString()
    {
        $webhook = new Webhook();
        $this->assertEquals('', $webhook->getUrl());
        $this->assertEquals('', $webhook->getEvent());
    }

    /**
     * Verify the getters and setters of the webhook resource.
     *
     * @test
     *
     * @throws Exception
     */
    public function gettersAndSettersOfWebhookShouldBehaveAsExpected()
    {
        $webhook = new Webhook('https://dev.heidelpay.com', 'anEventIMadeUp');
        $this->assertEquals('https://dev.heidelpay.com', $webhook->getUrl());
        $this->assertEquals('anEventIMadeUp', $webhook->getEvent());

        $webhook->setUrl('https://docs.heidelpay.com');
        $webhook->setEvent('aDifferentEventIMadeUp');
        $this->assertEquals('https://docs.heidelpay.com', $webhook->getUrl());
        $this->assertEquals('aDifferentEventIMadeUp', $webhook->getEvent());
    }
}
