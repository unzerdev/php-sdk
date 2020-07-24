<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\test\BaseIntegrationTest;

class SetMetadataTest extends BaseIntegrationTest
{
    /**
     * Verify Metadata can be created and fetched with the API.
     *
     * @test
     */
    public function metadataShouldBeCreatableAndFetchableWithTheApi(): void
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
     */
    public function authorizeShouldCreateMetadata(): void
    {
        $metadata = new Metadata();
        $metadata->setShopType('Shopware');
        $metadata->setShopVersion('5.12');
        $metadata->addMetadata('ModuleType', 'Shopware 5');
        $metadata->addMetadata('ModuleVersion', '18.3.12');
        $this->assertEmpty($metadata->getId());

        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $this->heidelpay->authorize(1.23, 'EUR', $paypal, 'https://heidelpay.com', null, null, $metadata);
        $this->assertNotEmpty($metadata->getId());
    }

    /**
     * Verify metadata will automatically created on charge.
     *
     * @test
     */
    public function chargeShouldCreateMetadata(): void
    {
        $metadata = new Metadata();
        $metadata->setShopType('Shopware');
        $metadata->setShopVersion('5.12');
        $metadata->addMetadata('ModuleType', 'Shopware 5');
        $metadata->addMetadata('ModuleVersion', '18.3.12');
        $this->assertEmpty($metadata->getId());

        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $this->heidelpay->charge(1.23, 'EUR', $paymentType, 'https://heidelpay.com', null, null, $metadata);
        $this->assertNotEmpty($metadata->getId());
    }

    /**
     * Verify Metadata is fetched when payment is fetched.
     *
     * @test
     */
    public function paymentShouldFetchMetadataResourceOnFetch(): void
    {
        $metadata = (new Metadata())->addMetadata('key', 'value');

        /** @var Paypal $paymentType */
        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $paymentType->authorize(10.0, 'EUR', 'https://heidelpay.com', null, null, $metadata);
        $payment = $authorize->getPayment();
        $this->assertSame($metadata, $payment->getMetadata());

        $fetchedPayment = $this->heidelpay->fetchPayment($payment->getId());
        $fetchedMetadata = $fetchedPayment->getMetadata();
        $this->assertEquals($metadata->expose(), $fetchedMetadata->expose());
    }

    /**
     * Verify error is thrown when metadata is empty.
     *
     * @test
     */
    public function emptyMetaDataShouldLeadToError(): void
    {
        $metadata = new Metadata();
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_REQUEST_DATA_IS_INVALID);
        $this->heidelpay->createMetadata($metadata);
    }
}
