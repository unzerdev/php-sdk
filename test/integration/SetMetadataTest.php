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
 * @package  heidelpayPHP/tests/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class SetMetadataTest extends BasePaymentTest
{
    /**
     * Verify Metadata can be created and fetched with the API.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function metadataShouldBeCreatableAndFetchableWithTheApi()
    {
        $metadata = new Metadata();
        $this->assertNull($metadata->getShopType());
        $this->assertNull($metadata->getShopVersion());
        $this->assertNull($metadata->getMetadata('MyCustomData'));

        $metadata->setShopType('my awesome shop');
        $metadata->setShopVersion('v2.0.0');
        $metadata->addMetadata('MyCustomData', 'my custom information');
        $this->assertNull($metadata->getId());

        $this->heidelpay->createMetadata($metadata);
        $this->assertNotNull($metadata->getId());

        $fetchedMetadata = (new Metadata())->setParentResource($this->heidelpay)->setId($metadata->getId());
        $this->assertNull($fetchedMetadata->getShopType());
        $this->assertNull($fetchedMetadata->getShopVersion());
        $this->assertNull($fetchedMetadata->getMetadata('MyCustomData'));

        $this->heidelpay->fetchMetadata($fetchedMetadata);
        $this->assertEquals('my awesome shop', $fetchedMetadata->getShopType());
        $this->assertEquals('v2.0.0', $fetchedMetadata->getShopVersion());
        $this->assertEquals('my custom information', $fetchedMetadata->getMetadata('MyCustomData'));
    }

    /**
     * Verify metadata will automatically created on authorize.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldCreateMetadata()
    {
        $metadata = new Metadata();
        $metadata->setShopType('Shopware');
        $metadata->setShopVersion('5.12');
        $metadata->addMetadata('ModuleType', 'Shopware 5');
        $metadata->addMetadata('ModuleVersion', '18.3.12');
        $this->assertEmpty($metadata->getId());

        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $this->heidelpay->authorize(1.23, 'EUR', $card, 'https://heidelpay.com', null, null, $metadata);
        $this->assertNotEmpty($metadata->getId());
    }

    /**
     * Verify metadata will automatically created on charge.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldCreateMetadata()
    {
        $metadata = new Metadata();
        $metadata->setShopType('Shopware');
        $metadata->setShopVersion('5.12');
        $metadata->addMetadata('ModuleType', 'Shopware 5');
        $metadata->addMetadata('ModuleVersion', '18.3.12');
        $this->assertEmpty($metadata->getId());

        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $this->heidelpay->charge(1.23, 'EUR', $card, 'https://heidelpay.com', null, null, $metadata);
        $this->assertNotEmpty($metadata->getId());
    }

    /**
     * Verify Metadata is fetched when payment is fetched.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function paymentShouldFetchMetadataResourceOnFetch()
    {
        $metadata = (new Metadata())->addMetadata('key', 'value');

        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $card->authorize(10.0, 'EUR', 'https://heidelpay.com', null, null, $metadata);
        $payment = $authorize->getPayment();
        $this->assertSame($metadata, $payment->getMetadata());

        $fetchedPayment = $this->heidelpay->fetchPayment($payment->getId());
        $fetchedMetadata = $fetchedPayment->getMetadata();
        $this->assertEquals($metadata->expose(), $fetchedMetadata->expose());
    }

    /**
     * Verify metadata will automatically created and referenced on authorize.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldCreateMetadataEvenIfItHasNotBeenSet()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $this->heidelpay->authorize(1.23, 'EUR', $card, 'https://heidelpay.com');

        $metadata = $authorize->getPayment()->getMetadata();
        $this->assertNotEmpty($metadata->getId());

        $this->assertArraySubset(['metadata' => $metadata], $authorize->getLinkedResources());
    }

    /**
     * Verify metadata will automatically created and referenced on charge.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldCreateMetadataEvenIfItHasNotBeenSet()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(1.23, 'EUR', $card, 'https://heidelpay.com');

        $metadata = $charge->getPayment()->getMetadata();
        $this->assertNotEmpty($metadata->getId());

        $this->assertArraySubset(['metadata' => $metadata], $charge->getLinkedResources());
    }
}
