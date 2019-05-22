<?php
/**
 * This class defines unit tests to verify metadata functionalities.
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use stdClass;

class MetadataTest extends BasePaymentTest
{
    /**
     * Verify SDK-Data is initially set.
     *
     * @test
     *
     * @throws Exception
     */
    public function metadataShouldSetSDKDataAutomatically()
    {
        $metaData = new Metadata();
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaData->getSdkType());
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaData->getSdkVersion());
    }

    /**
     * Verify custom data can be set.
     *
     * @test
     *
     * @throws Exception
     */
    public function metaDataShouldAllowForCustomDataToBeSet()
    {
        $metaData = new Metadata();
        $metaData->addMetadata('myCustomData', 'Wow I can add custom information');
        $this->assertEquals('Wow I can add custom information', $metaData->getMetadata('myCustomData'));

        $this->assertNull($metaData->getMetadata('myCustomDataNo2'));
    }

    /**
     * Verify defined data can not be set.
     *
     * @test
     *
     * @throws Exception
     */
    public function metadataShouldNotAllowForMagicAccessToSdkAndShopData()
    {
        $metaData = new Metadata();
        $metaData->addMetadata('sdkType', 'sdkType');
        $metaData->addMetadata('sdkVersion', 'sdkVersion');
        $metaData->addMetadata('shopType', 'myShopType');
        $metaData->addMetadata('shopVersion', 'myShopVersion');

        $this->assertNotEquals('sdkType', $metaData->getSdkType());
        $this->assertNotEquals('sdkVersion', $metaData->getSdkVersion());
        $this->assertNotEquals('shopType', $metaData->getMetadata('shopType'));
        $this->assertNotEquals('shopVersion', $metaData->getMetadata('sdkVersion'));

        $this->assertEquals(Heidelpay::SDK_TYPE, $metaData->getSdkType());
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaData->getSdkVersion());
        $this->assertNull($metaData->getShopType());
        $this->assertNull($metaData->getShopVersion());
    }

    /**
     * Verify expose contains all defined data.
     *
     * @test
     *
     * @throws Exception
     */
    public function exposeShouldGatherAllDefinedDataInTheAnArray()
    {
        $metaData = new Metadata();
        $metaDataArray = $metaData->expose();
        $this->assertCount(2, $metaDataArray);
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaDataArray['sdkVersion']);
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaDataArray['sdkType']);

        $metaData->addMetadata('myData', 'This should be my Data');
        $metaData->addMetadata('additionalData', 'some information');
        $metaData->setShopType('my own shop');
        $metaData->setShopVersion('1.0.0.0');

        $metaDataArray = $metaData->expose();
        $this->assertCount(6, $metaDataArray);
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaDataArray['sdkVersion']);
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaDataArray['sdkType']);
        $this->assertEquals('my own shop', $metaDataArray['shopType']);
        $this->assertEquals('1.0.0.0', $metaDataArray['shopVersion']);
        $this->assertEquals('This should be my Data', $metaDataArray['myData']);
        $this->assertEquals('some information', $metaDataArray['additionalData']);
    }

    /**
     * Verify metadata can be updated.
     *
     * @test
     *
     * @throws Exception
     */
    public function handleResponseShouldUpdateMetadata()
    {
        $metaData = new Metadata();
        $metaData->addMetadata('myData', 'This should be my Data');
        $metaData->addMetadata('additionalData', 'some information');
        $metaData->setShopType('my own shop');
        $metaData->setShopVersion('1.0.0.0');

        $this->assertNull($metaData->getId());
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaData->getSdkVersion());
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaData->getSdkType());
        $this->assertEquals('my own shop', $metaData->getShopType());
        $this->assertEquals('1.0.0.0', $metaData->getShopVersion());
        $this->assertEquals('This should be my Data', $metaData->getMetadata('myData'));
        $this->assertEquals('some information', $metaData->getMetadata('additionalData'));
        $this->assertNull($metaData->getMetadata('extraData'));

        $response = new stdClass();
        $response->id = 'newId';
        $response->sdkType = 'newSdkType';
        $response->sdkVersion = 'newSdkVersion';
        $response->shopType = 'my new shop';
        $response->shopVersion = '1.0.0.1';
        $response->myData = 'This should be my new Data';
        $response->additionalData = 'some new information';
        $response->extraData = 'This is brand new information';

        $metaData->handleResponse($response);
        $this->assertEquals('newId', $metaData->getId());
        $this->assertEquals('newSdkType', $metaData->getSdkType());
        $this->assertEquals('newSdkVersion', $metaData->getSdkVersion());
        $this->assertEquals('my new shop', $metaData->getShopType());
        $this->assertEquals('1.0.0.1', $metaData->getShopVersion());
        $this->assertEquals('This should be my new Data', $metaData->getMetadata('myData'));
        $this->assertEquals('some new information', $metaData->getMetadata('additionalData'));
        $this->assertEquals('This is brand new information', $metaData->getMetadata('extraData'));
    }
}
