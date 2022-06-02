<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Webhook resource.
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
namespace UnzerSDK\test\unit\Resources;

use UnzerSDK\Resources\Webhook;
use UnzerSDK\test\BasePaymentTest;

class WebhookTest extends BasePaymentTest
{
    /**
     * Verify the constructor of the webhook resource behaves as expected.
     *
     * @test
     */
    public function mandatoryConstructorParametersShouldDefaultToEmptyString(): void
    {
        $webhook = new Webhook();
        $this->assertEquals('', $webhook->getUrl());
        $this->assertEquals('', $webhook->getEvent());
    }

    /**
     * Verify the getters and setters of the webhook resource.
     *
     * @test
     */
    public function gettersAndSettersOfWebhookShouldBehaveAsExpected(): void
    {
        $webhook = new Webhook('https://dev.unzer.com', 'anEventIMadeUp');
        $this->assertEquals('https://dev.unzer.com', $webhook->getUrl());
        $this->assertEquals('anEventIMadeUp', $webhook->getEvent());

        $webhook->setUrl('https://dev.unzer.com');
        $webhook->setEvent('aDifferentEventIMadeUp');
        $this->assertEquals('https://dev.unzer.com', $webhook->getUrl());
        $this->assertEquals('aDifferentEventIMadeUp', $webhook->getEvent());
    }
}
