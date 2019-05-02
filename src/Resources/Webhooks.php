<?php
/**
 * This class represents a group of Webhooks.
 * It is a pseudo resource used to manage bulk operations on webhooks.
 * It will never receive an id from the API.
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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\WebhookEvents;
use RuntimeException;
use stdClass;

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
    public function getWebhookList(): array
    {
        return $this->webhooks;
    }

    //</editor-fold>

    /**
     * @param stdClass $response
     * @param string   $method
     *
     * @throws RuntimeException
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        parent::handleResponse($response, $method);

        // there are multiple events in the response
        if (isset($response->events)) {
            $this->handleRegisteredWebhooks($response->events);
        }

        // it is only one event in the response
        if (isset($response->event)) {
            $this->handleRegisteredWebhooks([$response]);
        }
    }

    /**
     * Handles the given event array
     *
     * @param array $responseArray
     *
     * @throws RuntimeException
     */
    private function handleRegisteredWebhooks(array $responseArray = [])
    {
        $registeredWebhooks = [];

        foreach ($responseArray as $event) {
            $webhook = new Webhook();
            $webhook->setParentResource($this->getHeidelpayObject());
            $webhook->handleResponse($event);
            $registeredWebhooks[] = $webhook;
        }

        $this->webhooks = $registeredWebhooks;
    }
}
