<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Webhooks resource.
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

use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Resources\Webhooks;
use UnzerSDK\test\BasePaymentTest;
use stdClass;

class WebhooksTest extends BasePaymentTest
{
    /**
     * Verify the constructor of the webhooks resource behaves as expected.
     *
     * @test
     */
    public function mandatoryConstructorParametersShouldDefaultToEmptyString(): void
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
     */
    public function gettersAndSettersOfWebhookShouldBehaveAsExpected(): void
    {
        $webhook = new Webhooks('https://dev.unzer.com', [WebhookEvents::PAYMENT_COMPLETED]);
        $this->assertEquals('https://dev.unzer.com', $webhook->getUrl());
        $this->assertEquals([WebhookEvents::PAYMENT_COMPLETED], $webhook->getEventList());

        $webhook->setUrl('https://dev.unzer.com');
        $webhook->addEvent(WebhookEvents::CHARGE);
        $this->assertEquals('https://dev.unzer.com', $webhook->getUrl());
        $this->assertEquals([WebhookEvents::PAYMENT_COMPLETED, WebhookEvents::CHARGE], $webhook->getEventList());
    }

    /**
     * Verify the event adder of the webhooks resource does only allow valid webhook events.
     *
     * @test
     */
    public function adderOfWebhookEventsOnlyAllowsValidEvents(): void
    {
        $webhooks = new Webhooks('https://dev.unzer.com', []);
        $this->assertIsEmptyArray($webhooks->getEventList());

        $webhooks->setUrl('https://dev.unzer.com');
        $webhooks->addEvent('invalidEvent');
        $this->assertEquals('https://dev.unzer.com', $webhooks->getUrl());
        $this->assertIsEmptyArray($webhooks->getEventList());
    }

    /**
     * Verify response handling for more then one event in a webhooks request.
     *
     * @test
     */
    public function responseHandlingForEventsShouldBehaveAsExpected(): void
    {
        $webhooks = new Webhooks('https://dev.unzer.com', [WebhookEvents::CHARGE, WebhookEvents::AUTHORIZE]);
        $webhooks->setParentResource(new Unzer('s-priv-123'));
        $this->assertEquals('https://dev.unzer.com', $webhooks->getUrl());
        $this->assertEquals([WebhookEvents::CHARGE, WebhookEvents::AUTHORIZE], $webhooks->getEventList());

        $response = new stdClass();
        $eventA = new stdClass();
        $eventA->id = 's-whk-1084';
        $eventA->url = 'https://dev.unzer.com';
        $eventA->event = 'charge';
        $eventB = new stdClass();
        $eventB->id = 's-whk-1085';
        $eventB->url = 'https://dev.unzer.com';
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
        [$webhookA, $webhookB] = $webhookList;
        $this->assertInstanceOf(Webhook::class, $webhookA);
        $this->assertInstanceOf(Webhook::class, $webhookB);
        $this->assertEquals(
            ['event' => 'charge', 'id' => 's-whk-1084', 'url' => 'https://dev.unzer.com'],
            $webhookA->expose()
        );
        $this->assertEquals(
            ['event' => 'authorize', 'id' => 's-whk-1085', 'url' => 'https://dev.unzer.com'],
            $webhookB->expose()
        );
    }

    /**
     * Verify response handling of one event in a webhooks request.
     *
     * @test
     */
    public function responseHandlingForOneEventShouldBehaveAsExpected(): void
    {
        $webhooks = new Webhooks('https://dev.unzer.com', [WebhookEvents::CHARGE]);
        $webhooks->setParentResource(new Unzer('s-priv-123'));
        $this->assertEquals('https://dev.unzer.com', $webhooks->getUrl());
        $this->assertEquals([WebhookEvents::CHARGE], $webhooks->getEventList());

        $response = new stdClass();
        $response->id = 's-whk-1085';
        $response->url = 'https://docs.unzer.com';
        $response->event = 'authorize';

        $webhooks->handleResponse($response);
        $webhookList = $webhooks->getWebhookList();
        $this->assertCount(1, $webhookList);

        /** @var Webhook $webhook*/
        [$webhook] = $webhookList;
        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertEquals(
            ['event' => 'authorize', 'id' => 's-whk-1085', 'url' => 'https://docs.unzer.com'],
            $webhook->expose()
        );
    }
}
