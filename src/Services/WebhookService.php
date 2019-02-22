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
}
