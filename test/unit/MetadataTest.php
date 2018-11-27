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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Metadata;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class MetadataTest extends BasePaymentTest
{
    /**
     * Verify Metadata is automatically generated and added to the heidelpay object.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function heidelpayShouldAutomaticallyProvideAMetadataObject()
    {
        $metadata = $this->heidelpay->getMetadata();

        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Metadata::class, $metadata);
        $this->assertSame($this->heidelpay, $metadata->getParentResource());
    }

    /**
     * Verify SDK-Data is initially set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
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
     * @throws ExpectationFailedException
     */
    public function metaDataShouldAllowForCustomDataToBeSet()
    {
        $metaData = new Metadata();
        $metaData->set('myCustomData', 'Wow I can add custom information');
        $this->assertEquals('Wow I can add custom information', $metaData->get('myCustomData'));

        $this->assertNull($metaData->get('myCustomDataNo2'));
    }

    /**
     * Verify defined data can not be set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function metadataShouldNotAllowForMagicAccessToSdkAndShopData()
    {
        $metaData = new Metadata();
        $metaData->set('sdkType', 'sdkType');
        $metaData->set('sdkVersion', 'sdkVersion');
        $metaData->set('shopType', 'myShopType');
        $metaData->set('shopVersion', 'myShopVersion');

        $this->assertNotEquals('sdkType', $metaData->getSdkType());
        $this->assertNotEquals('sdkVersion', $metaData->getSdkVersion());
        $this->assertNotEquals('shopType', $metaData->get('shopType'));
        $this->assertNotEquals('shopVersion', $metaData->get('sdkVersion'));

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
     * @throws ExpectationFailedException
     */
    public function exposeShouldGatherAllDefinedDataInTheAnArray()
    {
        $metaData = new Metadata();
        $metaDataArray = $metaData->expose();
        $this->assertCount(2, $metaDataArray);
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaDataArray['sdkVersion']);
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaDataArray['sdkType']);

        $metaData->set('myData', 'This should be my Data');
        $metaData->set('additionalData', 'some information');
        $metaData->setShopType('my own shop');
        $metaData->setShopVersion('1.0.0.0-beta3');

        $metaDataArray = $metaData->expose();
        $this->assertCount(6, $metaDataArray);
        $this->assertEquals(Heidelpay::SDK_VERSION, $metaDataArray['sdkVersion']);
        $this->assertEquals(Heidelpay::SDK_TYPE, $metaDataArray['sdkType']);
        $this->assertEquals('my own shop', $metaDataArray['shopType']);
        $this->assertEquals('1.0.0.0-beta3', $metaDataArray['shopVersion']);
        $this->assertEquals('This should be my Data', $metaDataArray['myData']);
        $this->assertEquals('some information', $metaDataArray['additionalData']);
    }
}
