<?php
/**
 * This class represents a group of Webhooks.
 * It is a pseudo resource used to manage bulk operations on webhooks.
 * It will never receive an id friom the API.
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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\WebhookEvents;

class Webhooks extends AbstractHeidelpayResource
{
    /** @var string $url */
    protected $url;

    /** @var array $eventList */
    protected $eventList = [];

    /** @var array $webhooks */
    private $webhooks = [];

    /**
     * Webhook constructor.
     *
     * @param string $url
     * @param array  $eventList
     */
    public function __construct(string $url = '', array $eventList = [])
    {
        $this->url = $url;
        $this->eventList = $eventList;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Webhooks
     */
    public function setUrl(string $url): Webhooks
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return array
     */
    public function getEventList(): array
    {
        return $this->eventList;
    }

    /**
     * @param string $event
     *
     * @return Webhooks
     */
    public function addEvent(string $event): Webhooks
    {
        if (in_array($event, WebhookEvents::ALLOWED_WEBHOOKS, true) && !in_array($event, $this->eventList, true)) {
            $this->eventList[] = $event;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    /**
     * @param array $webhooks
     *
     * @return Webhooks
     */
    public function setWebhooks(array $webhooks): Webhooks
    {
        $this->webhooks = $webhooks;
        return $this;
    }

    //</editor-fold>

    /**
     * @param \stdClass $response
     * @param string    $method
     *
     * @throws \RuntimeException
     */
    public function handleResponse(\stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        parent::handleResponse($response, $method);

        if (isset($response->events)) {
            $registeredWebhooks = [];

            foreach ($response->events as $event) {
                $webhook = new Webhook();
                $webhook->setParentResource($this->getHeidelpayObject());
                $webhook->handleResponse($event, $method);
                $registeredWebhooks[] = $webhook;
            }

            $this->webhooks = $registeredWebhooks;
        }
    }
}
