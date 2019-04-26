<?php
/**
 * This class defines unit tests to verify functionality of the Webhooks resource.
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

use heidelpayPHP\Constants\WebhookEvents;
use heidelpayPHP\Resources\Webhooks;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class WebhooksTest extends BaseUnitTest
{
    /**
     * Verify the constructor of the webhooks resource behaves as expected.
     *
     * @test
     *
     * @throws Exception
     */
    public function mandatoryConstructorParametersShouldDefaultToEmptyString()
    {
        $webhook = new Webhooks();
        $this->assertEquals('', $webhook->getUrl());
        $this->assertIsEmptyArray($webhook->getEventList());
        $this->assertIsEmptyArray($webhook->getWebhookList());
    }

    /**
     * Verify the getters and setters of the webhooks resource.
     *
     * @test
     *
     * @throws Exception
     */
    public function gettersAndSettersOfWebhookShouldBehaveAsExpected()
    {
        $webhook = new Webhooks('https://dev.heidelpay.com', [WebhookEvents::PAYMENT_COMPLETED]);
        $this->assertEquals('https://dev.heidelpay.com', $webhook->getUrl());
        $this->assertArraySubset([WebhookEvents::PAYMENT_COMPLETED], $webhook->getEventList());

        $webhook->setUrl('https://docs.heidelpay.com');
        $webhook->addEvent(WebhookEvents::CHARGE);
        $this->assertEquals('https://docs.heidelpay.com', $webhook->getUrl());
        $this->assertArraySubset([WebhookEvents::PAYMENT_COMPLETED, WebhookEvents::CHARGE], $webhook->getEventList());
    }

    /**
     * Verify the event adder of the webhooks resource does only allow valid webhook events.
     *
     * @test
     *
     * @throws Exception
     */
    public function adderOfWebhookEventsOnlyAllowsValidEvents()
    {
        $webhook = new Webhooks('https://dev.heidelpay.com', []);
        $this->assertIsEmptyArray($webhook->getEventList());

        $webhook->setUrl('https://docs.heidelpay.com');
        $webhook->addEvent('invalidEvent');
        $this->assertEquals('https://docs.heidelpay.com', $webhook->getUrl());
        $this->assertIsEmptyArray($webhook->getEventList());
    }
}
