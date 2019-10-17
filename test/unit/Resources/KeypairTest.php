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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;
use stdClass;

class KeypairTest extends BaseUnitTest
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     */
    public function gettersAndSettersWorkAsExpected()
    {
        $keypair = new Keypair();
        $this->assertFalse($keypair->isDetailed());
        $this->assertNull($keypair->getPublicKey());
        $this->assertNull($keypair->getPrivateKey());
        $this->assertEmpty($keypair->getPaymentTypes());
        $this->assertSame($keypair->getPaymentTypes(), $keypair->getAvailablePaymentTypes());
        $this->assertNull($keypair->isCof());
        $this->assertEquals('', $keypair->getSecureLevel());
        $this->assertEquals('', $keypair->getMerchantName());
        $this->assertEquals('', $keypair->getMerchantAddress());
        $this->assertEquals('', $keypair->getAlias());
        $this->assertFalse($keypair->isDetailed());

        $keypair->setDetailed(true);

        $this->assertTrue($keypair->isDetailed());
    }

    /**
     * Verify that a key pair can be updated on handle response.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function aKeypairShouldBeUpdatedThroughResponseHandling()
    {
        $keypair = new Keypair();

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
        $this->assertArraySubset($paymentTypes, $keypair->getPaymentTypes());
        $this->assertEquals('s-pub-1234', $keypair->getPublicKey());
        $this->assertEquals('s-priv-4321', $keypair->getPrivateKey());
    }

    /**
     * Verify that a key pair can be updated with details on handle response.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function aKeypairShouldBeUpdatedWithDetailsThroughResponseHandling()
    {
        $keypair = new Keypair();

        $paymentTypes = [
            (object) [
                'supports' => [
                    (object) [
                        'brands' => ['JCB', 'VISAELECTRON', 'MAESTRO', 'VISA', 'MASTER'],
                        'countries' => [],
                        'channel' => '31HA07BC819430D3495C56BC18C55622',
                        'currency' => ['CHF', 'CNY', 'JPY', 'USD', 'GBP', 'EUR']
                    ]
                ],
                'type' => 'card',
                'allowCustomerTypes' => 'B2C',
                'allowCreditTransaction' => true,
                '3ds' => true
            ],
            (object) [
                'supports' => [
                    (object) [
                        'brands' => ['CUP', 'SOLO', 'CARTEBLEUE', 'VISAELECTRON', 'MAESTRO', 'AMEX', 'VISA', 'MASTER'],
                        'countries' => [],
                        'channel' => '31HA07BC819430D3495C7C9D07B1A922',
                        'currency' => ['MGA', 'USD', 'GBP', 'EUR']
                    ]
                ],
                'type' => 'card',
                'allowCustomerTypes' => 'B2C',
                'allowCreditTransaction' => true,
                '3ds' => false
            ]
        ];

        $testResponse = (object) [
            'publicKey' => 's-pub-1234',
            'privateKey' => 's-priv-4321',
            'secureLevel' => 'SAQ-D',
            'alias' => 'Readme.io user',
            'merchantName' => 'Heidelpay GmbH',
            'merchantAddress' => 'Vangerowstraße 18, 69115 Heidelberg',
            'paymentTypes' => $paymentTypes
            ];

        $keypair->handleResponse($testResponse);
        $this->assertEquals($paymentTypes, $keypair->getPaymentTypes());
        $this->assertSame($keypair->getAvailablePaymentTypes(), $keypair->getPaymentTypes());
        $this->assertEquals('s-pub-1234', $keypair->getPublicKey());
        $this->assertEquals('s-priv-4321', $keypair->getPrivateKey());
        $this->assertEquals('SAQ-D', $keypair->getSecureLevel());
        $this->assertEquals('Readme.io user', $keypair->getAlias());
        $this->assertEquals('Heidelpay GmbH', $keypair->getMerchantName());
        $this->assertEquals('Vangerowstraße 18, 69115 Heidelberg', $keypair->getMerchantAddress());
    }
}
