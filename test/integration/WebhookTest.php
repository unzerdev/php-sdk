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

use heidelpayPHP\Constants\WebhookEvents;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\test\BasePaymentTest;

class WebhookTest extends BasePaymentTest
{
    /**
     * Verify Webhook resource can be registered and fetched.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function webhookResourceShouldBeCreatableAndFetchable()
    {
        $webhook = new Webhook('https://www.heidelpay.de');
        $webhook->addEvent(WebhookEvents::ALL);
        $this->heidelpay->createWebhook($webhook);
        $this->assertNotNull($webhook->getId());
    }

    /**
     * Verify Webhook resource can be updated.
     *
     * @test
     */
    public function webhookResourceShouldBeUpdateable()
    {
    }

    /**
     * Verify Webhook resource can be updated.
     *
     * @test
     */
    public function webhookResourceShouldBeDeletable()
    {
    }
}
