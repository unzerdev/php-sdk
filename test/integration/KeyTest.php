<?php
/**
 * This class defines integration tests to verify keypair functionalities.
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
 * @package  heidelpay/mgw_sdk/test/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\SupportedLocales;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\ExpectationFailedException;

class KeyTest extends BasePaymentTest
{
    /**
     * Validate valid keys are accepted.
     *
     * @test
     * @dataProvider validKeysDataProvider
     *
     * @param string $key
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function validKeysShouldBeExcepted($key)
    {
        $heidelpay = new Heidelpay($key, SupportedLocales::GERMAN_GERMAN);
        $this->assertEquals($key, $heidelpay->getKey());
    }

    /**
     * Validate invalid keys are revoked.
     *
     * @test
     * @dataProvider invalidKeysDataProvider
     *
     * @param string $key
     *
     * @throws \RuntimeException
     */
    public function invalidKeysShouldResultInException($key)
    {
        $this->expectException(\RuntimeException::class);
        new Heidelpay($key, SupportedLocales::GERMAN_GERMAN);
    }

    /**
     * Verify key pair command can be performed.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function keypairShouldReturnExpectedValues()
    {
        $keypair = $this->heidelpay->fetchKeypair();
        $this->assertNotNull($keypair);
        $this->assertNotEmpty($keypair->getPublicKey());
        $this->assertNotEmpty($keypair->getAvailablePaymentTypes());
    }
}
