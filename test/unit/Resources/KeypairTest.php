<?php
/**
 * This class defines unit tests to verify functionality of the Keypair resource.
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\test\BaseUnitTest;
use RuntimeException;
use stdClass;

class KeypairTest extends BaseUnitTest
{
    /**
     * Verify that an Authorization can be updated on handle response.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function anAuthorizationShouldBeUpdatedThroughResponseHandling()
    {
        $keypair = new Keypair();
        $this->assertNull($keypair->getPublicKey());
        $this->assertNull($keypair->getPrivateKey());
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInternalType('array', $keypair->getAvailablePaymentTypes());
        $this->assertEmpty($keypair->getAvailablePaymentTypes());

        $paymentTypes = [
            'przelewy24',
            'ideal',
            'paypal',
            'prepayment',
            'invoice',
            'sepa-direct-debit-guaranteed',
            'card',
            'sofort',
            'invoice-guaranteed',
            'sepa-direct-debit',
            'giropay'
        ];

        $testResponse = new stdClass();
        $testResponse->publicKey = 's-pub-1234';
        $testResponse->privateKey = 's-priv-4321';
        $testResponse->availablePaymentTypes = $paymentTypes;

        $keypair->handleResponse($testResponse);
        $this->assertArraySubset($paymentTypes, $keypair->getAvailablePaymentTypes());
        $this->assertEquals('s-pub-1234', $keypair->getPublicKey());
        $this->assertEquals('s-priv-4321', $keypair->getPrivateKey());
    }
}
