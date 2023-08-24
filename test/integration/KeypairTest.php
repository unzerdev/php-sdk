<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify keypair functionalities.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\integration
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\Unzer;
use UnzerSDK\test\BaseIntegrationTest;
use RuntimeException;

class KeypairTest extends BaseIntegrationTest
{
    /**
     * Validate valid keys are accepted.
     *
     * @test
     *
     * @dataProvider validKeysDataProvider
     *
     * @param string $key
     */
    public function validKeysShouldBeExcepted($key): void
    {
        $unzer = new Unzer($key);
        $this->assertEquals($key, $unzer->getKey());
    }

    /**
     * Validate invalid keys are revoked.
     *
     * @test
     *
     * @dataProvider invalidKeysDataProvider
     *
     * @param string $key
     */
    public function invalidKeysShouldResultInException($key): void
    {
        $this->expectException(RuntimeException::class);
        new Unzer($key);
    }

    /**
     * Verify key pair config can be fetched.
     *
     * @test
     */
    public function keypairShouldReturnExpectedValues(): void
    {
        $keypair = $this->unzer->fetchKeypair();
        $this->assertNotNull($keypair);
        $this->assertNotEmpty($keypair->getPublicKey());
        $this->assertNotEmpty($keypair->getPrivateKey());
        $this->assertNotEmpty($keypair->getAvailablePaymentTypes());
        $this->assertNotEmpty($keypair->getSecureLevel());
    }

    /**
     * Verify key pair config can be fetched with details.
     *
     * @test
     */
    public function keypairShouldBeFetchableWithDetails(): void
    {
        $keypair = $this->unzer->fetchKeypair(true);
        $this->assertNotNull($keypair);
        $this->assertNotEmpty($keypair->getPublicKey());
        $this->assertNotEmpty($keypair->getPrivateKey());
        $this->assertNotEmpty($keypair->getPaymentTypes());
        $this->assertNotEmpty($keypair->getSecureLevel());
    }
}
