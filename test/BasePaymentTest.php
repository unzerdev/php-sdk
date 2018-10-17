<?php
/**
 * This class is the base class for all integration tests of this SDK.
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
namespace heidelpay\MgwPhpSdk\test;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Constants\SupportedLocale;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
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

    const RETURN_URL = 'http://vnexpress.vn';
    const PRIVATE_KEY = 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n';
    const PRIVATE_KEY_NOT_PCI_DDS_COMPLIANT = 's-priv-2a107CYZMp3UbyVPAuqWoxQHi9nFyeiW'; // todo replace

    /**
     * {@inheritDoc}
     *
     * @throws HeidelpaySdkException
     */
    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::PRIVATE_KEY, SupportedLocale::GERMAN_GERMAN);
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
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    public function createAuthorization(): Authorization
    {
        $card          = $this->heidelpay->createPaymentType($this->createCardObject());
        $orderId       = time();
        $authorization = $this->heidelpay->authorize(100.0, Currency::EURO, $card, self::RETURN_URL, null, $orderId);
        return $authorization;
    }

    /**
     * Creates and returns a Charge object with the API which can be used in test methods.
     *
     * @return Charge
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     */
    public function createCharge(): Charge
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, Currency::EURO, $card, self::RETURN_URL);
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
        $currencyReflection = new ReflectionClass(Currency::class);
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
