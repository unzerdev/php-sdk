<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method hire purchase direct debit.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\InstalmentPlans;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class HirePurchaseDirectDebitTest extends BasePaymentTest
{
    /**
     * Verify fetching instalment plans.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function instalmentPlansShouldBeSelectable()
    {
        // HirePurchaseDD wird lokal angelegt
        $hdd = (new HirePurchaseDirectDebit(null, null, null, null, null, null, null, null, null, null, null, null, null, null))->setParentResource($this->heidelpay);

        // Hire Purchase hat ein Child Plans
        $plans = (new InstalmentPlans(123.40, 'EUR', 4.99))->setParentResource($hdd);

        // Plans wird gefetched (die stdClasses der plans werden in einem array abgespeichert)
        $this->heidelpay->getResourceService()->fetch($plans);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        // Dann wird der gewünschte plan mit handleResponse
        $selectedPlan = $plans->getPlans()[1];
        $hdd->handleResponse($selectedPlan);

        foreach ($hdd->expose() as $key => $value) {
            $this->assertEquals($value, $selectedPlan->$key);
        }

        $hdd->setIban('DE46940594210000012345')->setAccountHolder('Manuel Weißmann');

        /** @var HirePurchaseDirectDebit $hdd */
        $hdd = $this->heidelpay->createPaymentType($hdd);

        $authorize = $hdd->authorize(123.4, 'EUR', self::RETURN_URL, $this->getMaximumCustomer(), null, null, $this->createBasket());
        $payment = $authorize->getPayment();
        $payment->charge();
    }

    /**
     * Verify hire purchase direct debit can be created with mandatory fields only.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function hirePurchaseDirectDebitShouldBeCreatableWithMandatoryFieldsOnly()
    {
        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly();

        $hirePurchaseDirectDebit = $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $hirePurchaseDirectDebit);
        $this->assertNotNull($hirePurchaseDirectDebit->getId());

        /** @var HirePurchaseDirectDebit $fetchedHirePurchaseDirectDebit */
        $fetchedHirePurchaseDirectDebit = $this->heidelpay->fetchPaymentType($hirePurchaseDirectDebit->getId());
        $this->assertEquals($hirePurchaseDirectDebit->expose(), $fetchedHirePurchaseDirectDebit->expose());
    }

    /**
     * Verify hire purchase direct debit can be created.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function hirePurchaseDirectDebitShouldBeCreatable()
    {
        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly();
        $hirePurchaseDirectDebit->setOrderDate($this->getCurrentDateString());
        $hirePurchaseDirectDebit = $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $hirePurchaseDirectDebit);
        $this->assertNotNull($hirePurchaseDirectDebit->getId());

        /** @var HirePurchaseDirectDebit $fetchedHirePurchaseDirectDebit */
        $fetchedHirePurchaseDirectDebit = $this->heidelpay->fetchPaymentType($hirePurchaseDirectDebit->getId());
        $this->assertInstanceOf(HirePurchaseDirectDebit::class, $fetchedHirePurchaseDirectDebit);
        $this->assertEquals($hirePurchaseDirectDebit->expose(), $fetchedHirePurchaseDirectDebit->expose());
    }

    /**
     * Verify charge is not allowed for hire purchase direct debit.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     * @throws \Exception
     */
    public function hirePurchaseDirectDebitShouldProhibitCharge()
    {
        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly();
        $hirePurchaseDirectDebit->setOrderDate('2011-04-12');
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_CHARGE_NOT_ALLOWED);

        $this->heidelpay->charge(
            100.38,
            'EUR',
            $hirePurchaseDirectDebit,
            self::RETURN_URL,
            $this->getMaximumCustomer(),
            null,
            null,
            $this->createBasket()
            );
    }

    /**
     * Verify Hire Purchase direct debit can be authorized.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \Exception
     */
    public function hirePurchaseDirectDebitShouldAllowAuthorize()
    {
        /** @var HirePurchaseDirectDebit $hdd */
        $hdd = $this->getHirePurchaseDirectDebitWithMandatoryFieldsOnly()->setOrderDate('2011-04-12');

        $basket    = $this->createBasket();
        $customer  = $this->getMaximumCustomer();
        $authorize = $this->heidelpay->authorize(123.40, 'EUR', $hdd, self::RETURN_URL, $customer, null, null, $basket);

        $this->assertNotEmpty($authorize->getId());
    }

    /**
     * Verify hire purchase direct debit will throw error if addresses do not match.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
//    public function hddShouldThrowErrorIfAddressesDoNotMatch()
//    {
//        $hirePurchaseDirectDebit = (new HirePurchaseDirectDebit('DE89370400440532013000', ));
//        $this->heidelpay->createPaymentType($hirePurchaseDirectDebit);
//
//        $this->expectException(HeidelpayApiException::class);
//        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);
//
//        $hirePurchaseDirectDebit->charge(
//            100.0,
//            'EUR',
//            self::RETURN_URL,
//            $this->getMaximumCustomerInclShippingAddress()
//        );
//    }

    //<editor-fold desc="Helper">

    /**
     * @return HirePurchaseDirectDebit
     *
     * @throws \Exception
     */
    private function getHirePurchaseDirectDebitWithMandatoryFieldsOnly(): HirePurchaseDirectDebit
    {
        /** @var HirePurchaseDirectDebit $hirePurchaseDirectDebit */
        $hirePurchaseDirectDebit = new HirePurchaseDirectDebit(
            'DE89370400440532013000',
            '',
            'Max Mustermann',
            3,
            $this->getCurrentDateString(),
            100.19,
            0.74,
            100.93,
            4.5,
            4.44,
            0,
            0,
            33.65,
            33.63
        );
        return $hirePurchaseDirectDebit;
    }

    //</editor-fold>
}
