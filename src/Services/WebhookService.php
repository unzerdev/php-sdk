<?php
/**
 * This service provides for all methods to manage webhooks/events.
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
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Resources\Webhooks;

class WebhookService
{
    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    /** @var ResourceService $resourceService */
    private $resourceService;

    /**
     * PaymentService constructor.
     *
     * @param Heidelpay $heidelpay
     */
    public function __construct(Heidelpay $heidelpay)
    {
        $this->heidelpay = $heidelpay;
        $this->resourceService = $heidelpay->getResourceService();
    }

    //<editor-fold desc="Webhook resource">

    /**
     * Creates Webhook resource
     *
     * @param Webhook $webhook
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function createWebhook(Webhook $webhook): Webhook
    {
        $webhook->setParentResource($this->heidelpay);
        $this->resourceService->create($webhook);
        return $webhook;
    }

    /**
     * Updates the given local Webhook object using the API.
     * Retrieves a Webhook resource, if the webhook parameter is the webhook id.
     *
     * @param Webhook|string $webhook
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchWebhook($webhook): Webhook
    {
        $webhookObject = $webhook;
        if (\is_string($webhook)) {
            $webhookObject = new Webhook();
            $webhookObject->setId($webhook);
        }

        $webhookObject->setParentResource($this->heidelpay);
        $this->resourceService->fetch($webhookObject);
        return $webhookObject;
    }

    /**
     * Updates the Webhook resource of the api with the given object.
     *
     * @param Webhook $webhook
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function updateWebhook($webhook): Webhook
    {
        $webhook->setParentResource($this->heidelpay);
        $this->resourceService->update($webhook);
        return $webhook;
    }

    /**
     * Deletes the given Webhook resource.
     *
     * @param Webhook|string $webhook
     *
     * @return Webhook|AbstractHeidelpayResource|null
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function deleteWebhook($webhook)
    {
        $webhookObject = $webhook;

        if (\is_string($webhook)) {
            $webhookObject = $this->fetchWebhook($webhook);
        }

        return $this->resourceService->delete($webhookObject);
    }

    //</editor-fold>

    //<editor-fold desc="Webhooks pseudo resource">
    /**
     * Fetches all registered webhook events and returns them in an array.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchWebhooks(): array
    {
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        $this->resourceService->fetch($webhooks);

        return $webhooks->getWebhooks();
    }

    /**
     * Deletes all registered webhooks.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function deleteWebhooks()
    {
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        $this->resourceService->delete($webhooks);
    }
    //</editor-fold>
}
