<?php
/**
 * This represents the Webhook resource.
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

use heidelpayPHP\Constants\WebhookEvents;

class Webhook extends AbstractHeidelpayResource
{
    /** @var string $url */
    protected $url;

    /** @var array $eventList */
    protected $eventList = [];

    /**
     * Webhook constructor.
     *
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;

        parent::__construct();
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
     * @return Webhook
     */
    public function setUrl(string $url): Webhook
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
     * @return Webhook
     */
    public function addEvent(string $event): Webhook
    {
        if (in_array($event, WebhookEvents::ALLOWED_WEBHOOKS, true) || !in_array($event, $this->eventList, true)) {
            $this->eventList[] = $event;
        }
        return $this;
    }

    /**
     * @param string $event
     *
     * @return Webhook
     */
    public function removeEvent(string $event): Webhook
    {
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Resource IF">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'webhooks';
    }

    //</editor-fold>
}
