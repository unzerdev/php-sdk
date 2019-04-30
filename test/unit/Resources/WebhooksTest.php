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
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Resources\Webhooks;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use RuntimeException;
use stdClass;

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
        $webhooks = new Webhooks();
        $this->assertEquals('', $webhooks->getUrl());
        $this->assertIsEmptyArray($webhooks->getEventList());
        $this->assertIsEmptyArray($webhooks->getWebhookList());
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
        $webhooks = new Webhooks('https://dev.heidelpay.com', []);
        $this->assertIsEmptyArray($webhooks->getEventList());

        $webhooks->setUrl('https://docs.heidelpay.com');
        $webhooks->addEvent('invalidEvent');
        $this->assertEquals('https://docs.heidelpay.com', $webhooks->getUrl());
        $this->assertIsEmptyArray($webhooks->getEventList());
    }

    /**
     * Verify response handling for more then one event in a webhooks request.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     */
    public function responseHandlingForEventsShouldBehaveAsExpected()
    {
        $webhooks = new Webhooks('https://dev.heidelpay.com', [WebhookEvents::CHARGE, WebhookEvents::AUTHORIZE]);
        $webhooks->setParentResource(new Heidelpay('s-priv-123'));
        $this->assertEquals('https://dev.heidelpay.com', $webhooks->getUrl());
        $this->assertEquals([WebhookEvents::CHARGE, WebhookEvents::AUTHORIZE], $webhooks->getEventList());

        $response = new stdClass();
        $eventA = new stdClass();
        $eventA->id = 's-whk-1084';
        $eventA->url = 'https://dev.heidelpay.com';
        $eventA->event = 'charge';
        $eventB = new stdClass();
        $eventB->id = 's-whk-1085';
        $eventB->url = 'https://dev.heidelpay.com';
        $eventB->event = 'authorize';
        $events = [$eventA, $eventB];

        $response->events = $events;

        $webhooks->handleResponse($response);
        $webhookList = $webhooks->getWebhookList();
        $this->assertCount(2, $webhookList);
        /**
         * @var Webhook $webhookA
         * @var Webhook $webhookB
         */
        list($webhookA, $webhookB) = $webhookList;
        $this->assertInstanceOf(Webhook::class, $webhookA);
        $this->assertInstanceOf(Webhook::class, $webhookB);
        $this->assertEquals(
            ['event' => 'charge', 'id' => 's-whk-1084', 'url' => 'https://dev.heidelpay.com'],
            $webhookA->expose()
        );
        $this->assertEquals(
            ['event' => 'authorize', 'id' => 's-whk-1085', 'url' => 'https://dev.heidelpay.com'],
            $webhookB->expose()
        );
    }

    /**
     * Verify response handling of one event in a webhooks request.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     */
    public function responseHandlingForOneEventShouldBehaveAsExpected()
    {
        $webhooks = new Webhooks('https://dev.heidelpay.com', [WebhookEvents::CHARGE]);
        $webhooks->setParentResource(new Heidelpay('s-priv-123'));
        $this->assertEquals('https://dev.heidelpay.com', $webhooks->getUrl());
        $this->assertEquals([WebhookEvents::CHARGE], $webhooks->getEventList());

        $response = new stdClass();
        $response->id = 's-whk-1085';
        $response->url = 'https://docs.heidelpay.de';
        $response->event = 'authorize';

        $webhooks->handleResponse($response);
        $webhookList = $webhooks->getWebhookList();
        $this->assertCount(1, $webhookList);

        /** @var Webhook $webhook*/
        list($webhook) = $webhookList;
        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertEquals(
            ['event' => 'authorize', 'id' => 's-whk-1085', 'url' => 'https://docs.heidelpay.de'],
            $webhook->expose()
        );
    }
}
