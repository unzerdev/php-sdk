<?php /** @noinspection UnnecessaryAssertionInspection */

/**
 * This class defines unit tests to verify functionality of the heidelpay class.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit;

use heidelpay\MgwPhpSdk\Constants\SupportedLocales;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Services\PaymentService;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class HeidelpayTest extends BaseUnitTest
{
    /**
     * Verify constructor works properly.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function constructorShouldInitPropertiesProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $this->assertInstanceOf(ResourceService::class, $heidelpay->getResourceService());
        $this->assertInstanceOf(PaymentService::class, $heidelpay->getPaymentService());
        $this->assertSame($heidelpay, $heidelpay->getPaymentService()->getHeidelpay());
        $this->assertEquals('s-priv-1234', $heidelpay->getKey());
        $this->assertEquals(SupportedLocales::USA_ENGLISH, $heidelpay->getLocale());

        $heidelpaySwiss = new Heidelpay('s-priv-1234', SupportedLocales::SWISS_GERMAN);
        $this->assertEquals(SupportedLocales::SWISS_GERMAN, $heidelpaySwiss->getLocale());

        $heidelpayGerman = new Heidelpay('s-priv-1234', SupportedLocales::GERMAN_GERMAN);
        $this->assertEquals(SupportedLocales::GERMAN_GERMAN, $heidelpayGerman->getLocale());
    }

    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $heidelpay->setLocale('myLocale');
        $this->assertEquals('myLocale', $heidelpay->getLocale());

        try {
            $heidelpay->setKey('söiodufhreoöhf');
            $this->assertTrue(false, 'This exception should have been thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Illegal key: Use a valid private key with this SDK!', $e->getMessage());
        }

        $resourceSrv = new ResourceService($heidelpay);
        $heidelpay->setResourceService($resourceSrv);
        $this->assertSame($resourceSrv, $heidelpay->getResourceService());

        $paymentSrv = new PaymentService($heidelpay);
        $heidelpay->setPaymentService($paymentSrv);
        $this->assertSame($paymentSrv, $heidelpay->getPaymentService());

        $this->assertFalse($heidelpay->isDebugMode());
        $heidelpay->setDebugMode(true);
        $this->assertTrue($heidelpay->isDebugMode());
        $heidelpay->setDebugMode(false);
        $this->assertFalse($heidelpay->isDebugMode());

        $this->assertNull($heidelpay->getDebugHandler());
        $dummyDebugHandler = new DummyDebugHandler();
        $heidelpay->setDebugHandler($dummyDebugHandler);
        $this->assertSame($dummyDebugHandler, $heidelpay->getDebugHandler());

        $this->assertEquals('', $heidelpay->getUri());
    }
}
