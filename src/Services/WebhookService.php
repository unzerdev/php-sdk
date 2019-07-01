<?php
/**
 * This service provides for all methods to manage webhooks/events.
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
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Resources\Webhooks;
use function is_string;
use RuntimeException;

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

    //<editor-fold desc="Getters/Setters">

    /**
     * @return Heidelpay
     */
    public function getHeidelpay(): Heidelpay
    {
        return $this->heidelpay;
    }

    /**
     * @param Heidelpay $heidelpay
     *
     * @return WebhookService
     */
    public function setHeidelpay(Heidelpay $heidelpay): WebhookService
    {
        $this->heidelpay = $heidelpay;
        return $this;
    }

    /**
     * @return ResourceService
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * @param ResourceService $resourceService
     *
     * @return WebhookService
     */
    public function setResourceService(ResourceService $resourceService): WebhookService
    {
        $this->resourceService = $resourceService;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Webhook resource">

    /**
     * Creates Webhook resource
     *
     * @param string $url   The url the registered webhook event should be send to.
     * @param string $event The event to be registered.
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createWebhook(string $url, string $event): Webhook
    {
        $webhook = new Webhook($url, $event);
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
     * @throws RuntimeException
     */
    public function fetchWebhook($webhook): Webhook
    {
        $webhookObject = $webhook;
        if (is_string($webhook)) {
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
     * @throws RuntimeException
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
     * @throws RuntimeException
     */
    public function deleteWebhook($webhook)
    {
        $webhookObject = $webhook;

        if (is_string($webhook)) {
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
     * @throws RuntimeException
     */
    public function fetchWebhooks(): array
    {
        /** @var Webhooks $webhooks */
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        $webhooks = $this->resourceService->fetch($webhooks);

        return $webhooks->getWebhookList();
    }

    /**
     * Deletes all registered webhooks.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function deleteWebhooks()
    {
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        $this->resourceService->delete($webhooks);
    }

    /**
     * Registers multiple Webhook events at once.
     *
     * @param string $url    The url the registered webhook events should be send to.
     * @param array  $events The events to be registered.
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createWebhooks(string $url, array $events): array
    {
        /** @var Webhooks $webhooks */
        $webhooks = new Webhooks($url, $events);
        $webhooks->setParentResource($this->heidelpay);
        $webhooks = $this->resourceService->create($webhooks);

        return $webhooks->getWebhookList();
    }

    //</editor-fold>

    //<editor-fold desc="Event handling">

    /**
     * Fetches the resource corresponding to the given eventData.
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchResourceByWebhookEvent(): AbstractHeidelpayResource
    {
        $resourceObject = null;
        $postData = $this->readInputStream();
        $eventData = json_decode($postData, false);
        $retrieveUrl = $eventData->retrieveUrl ?? null;

        if (!empty($retrieveUrl)) {
            $this->heidelpay->debugLog('Received event: ' . json_encode($eventData)); // encode again to uglify json
            $resourceObject = $this->resourceService->fetchResourceByUrl($retrieveUrl);
        }

        if (!$resourceObject instanceof AbstractHeidelpayResource) {
            throw new RuntimeException('Error fetching resource!');
        }

        return $resourceObject;
    }

    /**
     * Read and return the input stream.
     *
     * @return false|string
     */
    public function readInputStream()
    {
        return file_get_contents('php://input');
    }

    //</editor-fold>
}
