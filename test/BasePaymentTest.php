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
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Interfaces\PaymentInterface;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\TestCase;

class BasePaymentTest extends TestCase
{
    use CustomerFixtureTrait;

    /** @var Heidelpay $heidelpay */
    protected $heidelpay;

    const RETURN_URL = 'http://vnexpress.vn';
    const PRIVATE_KEY = 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n';
    const PRIVATE_KEY_NOT_PCI_DDS_COMPLIANT = 's-priv-2a107CYZMp3UbyVPAuqWoxQHi9nFyeiW'; // todo replace
    const PUBLIC_KEY = 's-pub-uM8yNmBNcs1GGdwAL4ytebYA4HErD22H';

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::PRIVATE_KEY, SupportedLocale::GERMAN_GERMAN);
    }

    //<editor-fold desc="Helpers">

    /**
     * @return Card
     */
    protected function createCard(): Card
    {
        /** @var Card $card */
        $card = new Card('4444333322221111', '03/20');
        $card->setCvc('123');
        return $card;
    }

    /**
     * @return Payment
     */
    protected function createPayment(): Payment
    {
        return new Payment($this->heidelpay);
    }

    /**
     * @param PaymentInterface $payment
     * @param float            $expectedRemaining
     * @param float            $expectedCharged
     * @param float            $expectedTotal
     * @param float            $expectedCanceled
     */
    protected function assertAmounts(
        $payment,
        $expectedRemaining,
        $expectedCharged,
        $expectedTotal,
        $expectedCanceled
    ) {
        $this->assertEquals($expectedRemaining, $payment->getRemaining(), 'The remaining amount does not match.');
        $this->assertEquals($expectedCharged, $payment->getCharged(), 'The charged amount does not match.');
        $this->assertEquals($expectedTotal, $payment->getTotal(), 'The total amount does not match.');
        $this->assertEquals($expectedCanceled, $payment->getCanceled(), 'The canceled amount does not match.');
    }

    /**
     * Mask a credit card number.
     *
     * @param $number
     * @param string $maskSymbol
     *
     * @return string
     */
    protected function maskCreditCardNumber($number, $maskSymbol = '*'): string
    {
        return substr($number, 0, 6) . str_repeat($maskSymbol, \strlen($number) - 10) . substr($number, -4);
    }

    /**
     * @return Authorization
     */
    public function createAuthorization(): Authorization
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $this->heidelpay->authorize(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);
        return $authorization;
    }

    /**
     * @return Charge
     */
    public function createCharge(): Charge
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $charge = $this->heidelpay->charge(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);
        return $charge;
    }

    //</editor-fold>
}
