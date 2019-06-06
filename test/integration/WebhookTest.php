<?php
/**
 * This class defines integration tests to verify Webhook features.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\WebhookEvents;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class WebhookTest extends BasePaymentTest
{
    //<editor-fold desc="Webhook tests">

    /**
     * Verify Webhook resource can be registered and fetched.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function webhookResourceCanBeRegisteredAndFetched(): Webhook
    {
        $url     = $this->generateUniqueUrl();
        $webhook = $this->heidelpay->createWebhook($url, WebhookEvents::ALL);
        $this->assertNotNull($webhook->getId());

        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals($webhook->expose(), $fetchedWebhook->expose());

        return $webhook;
    }

    /**
     * Verify Webhook url can be updated.
     *
     * @depends webhookResourceCanBeRegisteredAndFetched
     * @test
     *
     * @param Webhook $webhook
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws RuntimeException
     */
    public function webhookUrlShouldBeUpdateable(Webhook $webhook)
    {
        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::ALL, $fetchedWebhook->getEvent());

        $url = $this->generateUniqueUrl();
        $webhook->setUrl($url);
        $this->heidelpay->updateWebhook($webhook);

        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals($url, $fetchedWebhook->getUrl());
    }

    /**
     * Verify Webhook event can not be updated.
     *
     * @depends webhookResourceCanBeRegisteredAndFetched
     * @test
     *
     * @param Webhook $webhook
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws RuntimeException
     */
    public function webhookEventShouldNotBeUpdateable(Webhook $webhook)
    {
        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::ALL, $fetchedWebhook->getEvent());

        $webhook->setEvent(WebhookEvents::CUSTOMER);
        $this->heidelpay->updateWebhook($webhook);

        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::ALL, $fetchedWebhook->getEvent());
    }

    /**
     * Verify Webhook resource can be deleted.
     *
     * @depends webhookResourceCanBeRegisteredAndFetched
     * @test
     *
     * @param Webhook $webhook
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function webhookResourceShouldBeDeletable(Webhook $webhook)
    {
        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::ALL, $fetchedWebhook->getEvent());

        $this->assertNull($this->heidelpay->deleteWebhook($webhook));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_WEBHOOK_CAN_NOT_BE_FOUND);
        $this->heidelpay->fetchWebhook($webhook->getId());
    }

    /**
     * Verify webhook create will throw error when the event is already registered for the given URL.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function webhookCreateShouldThrowErrorWhenEventIsAlreadyRegistered()
    {
        $url     = $this->generateUniqueUrl();
        $this->heidelpay->createWebhook($url, WebhookEvents::ALL);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_WEBHOOK_EVENT_ALREADY_REGISTERED);
        $this->heidelpay->createWebhook($url, WebhookEvents::ALL);
    }

    //</editor-fold>

    //<editor-fold desc="Webhooks test">

    /**
     * Verify fetching all registered webhooks will return an array of webhooks.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchWebhooksShouldReturnArrayOfRegisteredWebhooks()
    {
        // --- Prepare --> remove all existing webhooks
        // start workaround - avoid error deleting non existing webhooks
        $this->heidelpay->createWebhook($this->generateUniqueUrl(), WebhookEvents::CUSTOMER);
        // end workaround - avoid error deleting non existing webhooks

        $this->heidelpay->deleteAllWebhooks();
        $webhooks = $this->heidelpay->fetchAllWebhooks();
        $this->assertCount(0, $webhooks);

        // --- Create some test webhooks ---
        $webhook1 = $this->heidelpay->createWebhook($this->generateUniqueUrl(), WebhookEvents::CUSTOMER);
        $webhook2 = $this->heidelpay->createWebhook($this->generateUniqueUrl(), WebhookEvents::CHARGE);
        $webhook3 = $this->heidelpay->createWebhook($this->generateUniqueUrl(), WebhookEvents::AUTHORIZE);

        // --- Verify webhooks have been registered ---
        $fetchedWebhooks = $this->heidelpay->fetchAllWebhooks();
        $this->assertCount(3, $fetchedWebhooks);

        $this->assertTrue($this->arrayContainsWebhook($fetchedWebhooks, $webhook1));
        $this->assertTrue($this->arrayContainsWebhook($fetchedWebhooks, $webhook2));
        $this->assertTrue($this->arrayContainsWebhook($fetchedWebhooks, $webhook3));
    }

    /**
     * Verify all webhooks can be removed at once.
     *
     * @test
     *
     * @depends webhookResourceCanBeRegisteredAndFetched
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function allWebhooksShouldBeRemovableAtOnce()
    {
        // --- Verify webhooks have been registered ---
        $webhooks = $this->heidelpay->fetchAllWebhooks();
        $this->assertGreaterThan(0, count($webhooks));

        // --- Verify all webhooks can be removed at once ---
        $this->heidelpay->deleteAllWebhooks();
        $webhooks = $this->heidelpay->fetchAllWebhooks();
        $this->assertCount(0, $webhooks);
    }

    /**
     * Verify setting multiple events at once.
     *
     * @test
     * @depends allWebhooksShouldBeRemovableAtOnce
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function bulkSettingWebhookEventsShouldBePossible()
    {
        $webhookEvents      = [WebhookEvents::AUTHORIZE, WebhookEvents::CHARGE, WebhookEvents::SHIPMENT];
        $url                = $this->generateUniqueUrl();
        $registeredWebhooks = $this->heidelpay->registerMultipleWebhooks($url, $webhookEvents);

        // check whether the webhooks have the correct url
        $registeredEvents = [];
        foreach ($registeredWebhooks as $webhook) {
            /** @var Webhook $webhook */
            if (in_array($webhook->getEvent(), $webhookEvents, true)) {
                $this->assertEquals($url, $webhook->getUrl());
            }
            $registeredEvents[] = $webhook->getEvent();
        }

        // check whether all of the webhookEvents exist
        sort($webhookEvents);
        sort($registeredEvents);
        $this->assertArraySubset($webhookEvents, $registeredEvents);
    }

    /**
     * Verify setting one event with bulk setting.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function bulkSettingOnlyOneWebhookShouldBePossible()
    {
        // remove all existing webhooks a avoid errors here
        $this->heidelpay->deleteAllWebhooks();

        $url                = $this->generateUniqueUrl();
        $registeredWebhooks = $this->heidelpay->registerMultipleWebhooks($url, [WebhookEvents::AUTHORIZE]);

        $this->assertCount(1, $registeredWebhooks);

        /** @var Webhook $webhook */
        $webhook = $registeredWebhooks[0];

        $this->assertEquals(WebhookEvents::AUTHORIZE, $webhook->getEvent());
        $this->assertEquals($url, $webhook->getUrl());
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Returns a unique url based on the current timestamp.
     *
     * @return string
     */
    private function generateUniqueUrl(): string
    {
        return 'https://www.heidelpay.de?test=' . str_replace([' ', '.'], '', microtime());
    }

    /**
     * Returns true if the given Webhook exists in the given array.
     *
     * @param $webhooksArray
     * @param Webhook $webhook
     *
     * @return bool
     */
    private function arrayContainsWebhook($webhooksArray, Webhook $webhook): bool
    {
        $arrayContainsWebhook = false;
        foreach ($webhooksArray as $webhookFromArray) {
            /** @var Webhook $webhookFromArray */
            if ($webhookFromArray->expose() === $webhook->expose()) {
                $arrayContainsWebhook = true;
                break;
            }
        }
        return $arrayContainsWebhook;
    }

    //</editor-fold>
}
