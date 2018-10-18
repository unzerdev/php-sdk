<?php
/**
 * This class defines integration tests to verify keypair functionalities.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class KeypairTest extends BasePaymentTest
{
    /**
     * Verify key pair command can be performed.
     *
     * @test
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function keypairShouldReturnExpectedValues()
    {
        $keypair = $this->heidelpay->fetchKeypair();
        $this->assertNotNull($keypair);
        $this->assertNotEmpty($keypair->getPublicKey());
        $this->assertNotEmpty($keypair->getAvailablePaymentTypes());
    }
}
