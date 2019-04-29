<?php
/**
 * This class defines unit tests to verify functionality of the webhook service.
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Services;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\WebhookService;
use heidelpayPHP\test\BaseUnitTest;
use ReflectionException;
use RuntimeException;

class WebhooksServiceTest extends BaseUnitTest
{
    /**
     * Verify setters and getters work properly.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $webhookService = new WebhookService($heidelpay);
        $this->assertSame($heidelpay, $webhookService->getHeidelpay());
        $this->assertSame($heidelpay->getResourceService(), $webhookService->getResourceService());

        $heidelpay2 = new Heidelpay('s-priv-1234');
        $resourceService2 = new ResourceService($heidelpay2);
        $webhookService->setResourceService($resourceService2);
        $this->assertSame($heidelpay, $webhookService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $webhookService->getResourceService());
        $this->assertSame($resourceService2, $webhookService->getResourceService());

        $webhookService->setHeidelpay($heidelpay2);
        $this->assertSame($heidelpay2, $webhookService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $webhookService->getResourceService());
    }

    /**
     * Verify create webhook calls resource service with webhook object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @throws ReflectionException
     */
    public function createWebhookShouldCallResourceServiceWithWebhookObject()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $webhookService = new WebhookService($heidelpay);
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        /** @var ResourceService $resourceServiceMock */
        $webhookService->setResourceService($resourceServiceMock);
        $resourceServiceMock->expects($this->once())->method('create')->with($this->callback(
            static function ($param) use ($heidelpay) {
                return $param instanceof Webhook &&
                       $param->getUrl() === 'myUrlString' &&
                       $param->getEvent() === 'TestEvent' &&
                       $param->getHeidelpayObject() === $heidelpay;
            }
        ));

        $webhookService->createWebhook('myUrlString', 'TestEvent');
    }
}
