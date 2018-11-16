<?php
/**
 * This class defines unit tests to verify functionality of the base payment type.
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
namespace heidelpay\MgwPhpSdk\test\unit\Resources\PaymentTypes;

use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Prepayment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebit;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class BasePaymentTypeTest extends TestCase
{
    /**
     * Verify the resource paths of the paymentTypes.
     *
     * @test
     * @dataProvider resourcePathDataProvider
     *
     * @param BasePaymentType $paymentType
     * @param string          $expectedPath
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function verifyTheResourcePathIsConstructedAsRequired(BasePaymentType $paymentType, $expectedPath)
    {
        $this->assertEquals($expectedPath, $paymentType->getResourcePath());
    }

    //<editor-fold desc="Data Provider">

    /**
     * @return array
     *
     * @throws \RuntimeException
     */
    public function resourcePathDataProvider(): array
    {
        return [
            [new Card(null, null), 'types/card'],
            [new Giropay(), 'types/giropay'],
            [new Ideal(null), 'types/ideal'],
            [new InvoiceGuaranteed(), 'types/invoice-guaranteed'],
            [new Invoice(), 'types/invoice'],
            [new Paypal(), 'types/paypal'],
            [new Prepayment(), 'types/prepayment'],
            [new Przelewy24(), 'types/przelewy24'],
            [new SepaDirectDebitGuaranteed(null), 'types/sepa-direct-debit-guaranteed'],
            [new SepaDirectDebit(null), 'types/sepa-direct-debit'],
            [new Sofort(), 'types/sofort'],
        ];
    }

    //</editor-fold>
}
