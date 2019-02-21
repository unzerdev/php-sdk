<?php
/**
 * This class defines integration tests to verify Webhook features.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\WebhookEvents;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;

class WebhookTest extends BasePaymentTest
{
    //<editor-fold desc="Tests">

    /**
     * Verify Webhook resource can be registered and fetched.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function webhookResourceCanBeRegisteredAndFetched(): Webhook
    {
        $url     = $this->generateUniqueUrl();
        $webhook = new Webhook($url, WebhookEvents::ALL);
        $this->heidelpay->createWebhook($webhook);
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
     * @throws \RuntimeException
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
     * Verify Webhook event can be updated.
     *
     * @depends webhookResourceCanBeRegisteredAndFetched
     * @test
     *
     * @param Webhook $webhook
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws \RuntimeException
     *
     * @group skip
     */
    public function webhookEventShouldBeUpdateable(Webhook $webhook)
    {
        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::ALL, $fetchedWebhook->getEvent());

        $webhook->setEvent(WebhookEvents::CUSTOMER);
        $this->heidelpay->updateWebhook($webhook);

        $fetchedWebhook = $this->heidelpay->fetchWebhook($webhook->getId());
        $this->assertEquals(WebhookEvents::CUSTOMER, $fetchedWebhook->getEvent());
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
     * @throws \RuntimeException
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

    //</editor-fold>
}
