<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method invoice guaranteed.
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
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class InvoiceGuaranteedTest extends BasePaymentTest
{
    /**
     * Verifies invoice guaranteed payment type can be created.
     *
     * @test
     *
     * @return InvoiceGuaranteed
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function invoiceGuaranteedTypeShouldBeCreatable(): InvoiceGuaranteed
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $invoiceGuaranteed);
        $this->assertNotNull($invoiceGuaranteed->getId());

        return $invoiceGuaranteed;
    }

    /**
     * Verify invoice guaranteed can be shipped.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function verifyInvoiceGuaranteedShipment(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $authorization = $invoiceGuaranteed->authorize(
            100.0,
            Currencies::EURO,
            self::RETURN_URL,
            $this->getMaximumCustomer()
        );
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertNotEmpty($authorization->getIban());
        $this->assertNotEmpty($authorization->getBic());
        $this->assertNotEmpty($authorization->getHolder());
        $this->assertNotEmpty($authorization->getDescriptor());


        $shipment = $this->heidelpay->ship($authorization->getPayment());
        $this->assertNotNull($shipment);
        $this->assertNotEmpty($shipment->getId());
    }

    /**
     * Verify that an invoice guaranteed object can be fetched from the api.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function invoiceGuaranteedTypeCanBeFetched(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $fetchedInvoiceGuaranteed = $this->heidelpay->fetchPaymentType($invoiceGuaranteed->getId());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $fetchedInvoiceGuaranteed);
        $this->assertEquals($invoiceGuaranteed->getId(), $fetchedInvoiceGuaranteed->getId());
    }
}
