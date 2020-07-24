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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\ResourceServiceInterface;
use heidelpayPHP\Interfaces\WebhookServiceInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Resources\Webhooks;
use function is_string;
use RuntimeException;

class WebhookService implements WebhookServiceInterface
{
    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    /** @var ResourceServiceInterface $resourceService */
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
     * @return ResourceServiceInterface
     */
    public function getResourceService(): ResourceServiceInterface
    {
        return $this->resourceService;
    }

    /**
     * @param ResourceServiceInterface $resourceService
     *
     * @return WebhookService
     */
    public function setResourceService(ResourceServiceInterface $resourceService): WebhookService
    {
        $this->resourceService = $resourceService;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Webhook resource">

    /**
     * {@inheritDoc}
     */
    public function createWebhook(string $url, string $event): Webhook
    {
        $webhook = new Webhook($url, $event);
        $webhook->setParentResource($this->heidelpay);
        $this->resourceService->createResource($webhook);
        return $webhook;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchWebhook($webhook): Webhook
    {
        $webhookObject = $webhook;
        if (is_string($webhook)) {
            $webhookObject = new Webhook();
            $webhookObject->setId($webhook);
        }

        $webhookObject->setParentResource($this->heidelpay);
        $this->resourceService->fetchResource($webhookObject);
        return $webhookObject;
    }

    /**
     * {@inheritDoc}
     */
    public function updateWebhook($webhook): Webhook
    {
        $webhook->setParentResource($this->heidelpay);
        $this->resourceService->updateResource($webhook);
        return $webhook;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteWebhook($webhook)
    {
        $webhookObject = $webhook;

        if (is_string($webhook)) {
            $webhookObject = $this->fetchWebhook($webhook);
        }

        return $this->resourceService->deleteResource($webhookObject);
    }

    //</editor-fold>

    //<editor-fold desc="Webhooks pseudo resource">

    /**
     * {@inheritDoc}
     */
    public function fetchAllWebhooks(): array
    {
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        /** @var Webhooks $webhooks */
        $webhooks = $this->resourceService->fetchResource($webhooks);

        return $webhooks->getWebhookList();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAllWebhooks(): void
    {
        $webhooks = new Webhooks();
        $webhooks->setParentResource($this->heidelpay);
        $this->resourceService->deleteResource($webhooks);
    }

    /**
     * {@inheritDoc}
     */
    public function registerMultipleWebhooks(string $url, array $events): array
    {
        $webhooks = new Webhooks($url, $events);
        $webhooks->setParentResource($this->heidelpay);
        /** @var Webhooks $webhooks */
        $webhooks = $this->resourceService->createResource($webhooks);

        return $webhooks->getWebhookList();
    }

    //</editor-fold>

    //<editor-fold desc="Event handling">

    /**
     * {@inheritDoc}
     */
    public function fetchResourceFromEvent($eventJson = null): AbstractHeidelpayResource
    {
        $resourceObject = null;
        $eventData = json_decode($eventJson ?? $this->readInputStream(), false);
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
