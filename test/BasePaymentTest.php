<?php
/**
 * This class is the base class for all integration tests of this SDK.
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
 * @package  heidelpay/mgw_sdk/test/integration
 */
namespace heidelpay\MgwPhpSdk\test;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Constants\SupportedLocales;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BasePaymentTest extends TestCase
{
    use CustomerFixtureTrait;

    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const RETURN_URL = 'http://dev.heidelpay.com';

    // SAQ-D certified merchants are allowed to handle and store CreditCard data,
    // thus can create a CreditCard via this SDK.
    // If the merchant is not certified to handle the CreditCard data SAQ-A applies
    // in which case the merchant has to embed our iFrame via JS (UIComponents).
    const PRIVATE_KEY_SAQ_D= 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n';
    const PUBLIC_KEY_SAQ_D = 's-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa';
    const PRIVATE_KEY_SAQ_A = 's-priv-2a10an6aJK0Jg7sMdpu9gK7ih8pCccze';
    const PUBLIC_KEY_SAQ_A = 's-pub-2a10nxkuA4lC7bIRtz2hKcFGeHhlkr2e';

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     */
    protected function setUp()
    {
        $this->heidelpay = (new Heidelpay(self::PRIVATE_KEY_SAQ_D, SupportedLocales::GERMAN_GERMAN))
            ->setDebugHandler(new TestDebugHandler())->setDebugMode(true);
    }

    //<editor-fold desc="Custom asserts">

    /**
     * @param Payment $payment
     * @param float   $expectedRemaining
     * @param float   $expectedCharged
     * @param float   $expectedTotal
     * @param float   $expectedCanceled
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    protected function assertAmounts(
        $payment,
        $expectedRemaining,
        $expectedCharged,
        $expectedTotal,
        $expectedCanceled
    ) {
        $amount = $payment->getAmount();
        $this->assertEquals($expectedRemaining, $amount->getRemaining(), 'The remaining amount does not match.');
        $this->assertEquals($expectedCharged, $amount->getCharged(), 'The charged amount does not match.');
        $this->assertEquals($expectedTotal, $amount->getTotal(), 'The total amount does not match.');
        $this->assertEquals($expectedCanceled, $amount->getCanceled(), 'The canceled amount does not match.');
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Mask a credit card number.
     *
     * @param $number
     * @param string $maskSymbol
     *
     * @return string
     */
    protected function maskNumber($number, $maskSymbol = '*'): string
    {
        return substr($number, 0, 6) . str_repeat($maskSymbol, \strlen($number) - 10) . substr($number, -4);
    }

    /**
     * Creates a Card object for tests.
     *
     * @return Card
     *
     * @throws \RuntimeException
     */
    protected function createCardObject(): Card
    {
        $card = new Card('4444333322221111', '03/20');
        $card->setCvc('123');
        return $card;
    }

    /**
     * Creates and returns an Authorization object with the API which can be used in test methods.
     *
     * @return Authorization
     *
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    public function createAuthorization(): Authorization
    {
        $card          = $this->heidelpay->createPaymentType($this->createCardObject());
        $orderId       = microtime(true);
        $authorization = $this->heidelpay->authorize(100.0, Currencies::EURO, $card, self::RETURN_URL, null, $orderId);
        return $authorization;
    }

    /**
     * Creates and returns a Charge object with the API which can be used in test methods.
     *
     * @return Charge
     *
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    public function createCharge(): Charge
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, Currencies::EURO, $card, self::RETURN_URL);
        return $charge;
    }

    //</editor-fold>

    //<editor-fold desc="DataProviders">

    /**
     * @return array
     *
     * @throws \ReflectionException
     */
    public function currencyCodeProvider(): array
    {
        $currencyReflection = new ReflectionClass(Currencies::class);
        $currencies         = $currencyReflection->getConstants();

        $keys          = array_keys($currencies);
        $values        = array_chunk($currencies, 1);
        return array_combine($keys, $values);
    }

    /**
     * Provides valid keys.
     *
     * @return array
     */
    public function validKeysDataProvider(): array
    {
        return [
            'private sandbox key' => ['s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'private production key' => ['p-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n']
        ];
    }

    /**
     * Provides invalid keys.
     *
     * @return array
     */
    public function invalidKeysDataProvider(): array
    {
        return [
            'public sandbox key' => ['s-pub-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'public production key' => ['p-pub-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid environment' => ['t-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid key type' => ['s-xyz-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid format 1' => ['spriv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n'],
            'invalid format 2' => ['2a102ZMq3gV4I3zJ888J7RR6u75oqK3n']
        ];
    }

    //</editor-fold>
}
