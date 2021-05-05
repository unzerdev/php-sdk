<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the embedded Applepay header resource.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\test\unit
 */
namespace UnzerSDK\test\unit\Resources\EmbeddedResources;

use UnzerSDK\Resources\EmbeddedResources\ApplePayHeader;
use PHPUnit\Framework\TestCase;

class ApplePayHeaderTest extends TestCase
{
    /**
     * Verify the resource data is set properly.
     *
     * @test
     */
    public function constructorShouldSetParameters(): void
    {
        $applepayHeader = new ApplePayHeader('ephemeralPublicKey', 'publicKeyHash', 'transactionId');

        $this->assertEquals('ephemeralPublicKey', $applepayHeader->getEphemeralPublicKey());
        $this->assertEquals('publicKeyHash', $applepayHeader->getPublicKeyHash());
        $this->assertEquals('transactionId', $applepayHeader->getTransactionId());
    }
}
