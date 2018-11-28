<?php
/**
 * This class defines integration tests to verify metadata functionalities.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Resources\Metadata;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class SetMetadataTest extends BasePaymentTest
{
    /**
     * Verify Metadata can be created and fetched with the API.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    public function metadataShouldBeCreatableAndFetchableWithTheApi()
    {
        $resourceService = $this->heidelpay->getResourceService();

        $metadata = new Metadata($this->heidelpay);
        $this->assertNull($metadata->getShopType());
        $this->assertNull($metadata->getShopVersion());
        $this->assertNull($metadata->get('MyCustomData'));

        $metadata->setShopType('my awesome shop');
        $metadata->setShopVersion('v2.0.0');
        $metadata->set('MyCustomData', 'my custom information');
        $this->assertNull($metadata->getId());

        $resourceService->create($metadata);
        $this->assertNotNull($metadata->getId());

        $fetchedMetadata = (new Metadata($this->heidelpay))->setId($metadata->getId());
        $this->assertNull($fetchedMetadata->getShopType());
        $this->assertNull($fetchedMetadata->getShopVersion());
        $this->assertNull($fetchedMetadata->get('MyCustomData'));

        $resourceService->fetch($fetchedMetadata);
        $this->assertEquals('my awesome shop', $fetchedMetadata->getShopType());
        $this->assertEquals('v2.0.0', $fetchedMetadata->getShopVersion());
        $this->assertEquals('my custom information', $fetchedMetadata->get('MyCustomData'));
    }
}
