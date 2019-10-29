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

use DateInterval;
use DateTime;
use Exception;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\InstalmentPlan;
use heidelpayPHP\test\BasePaymentTest;
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
    public function instalmentPlanShouldBeSelectable()
    {
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[1];
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann');
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());
    }

    /**
     * Verify fetching instalment plans.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function instalmentPlanSelectionWithAllFieldsSet()
    {
        $yesterday = (new DateTime())->add(DateInterval::createFromDateString('yesterday'));
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[1];
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX');
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());
    }

    /**
     * Verify Hire Purchase direct debit can be authorized.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function hirePurchaseDirectDebitShouldAllowAuthorize()
    {
        /** @var InstalmentPlan $plan */
        $plan = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99)->getPlans()[1];
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($plan, 'DE46940594210000012345', 'Manuel Weißmann');

        $authorize = $hdd->authorize(123.4, 'EUR', self::RETURN_URL, $this->getMaximumCustomer(), null, null, $this->createBasket());
        $payment = $authorize->getPayment();
        $payment->charge();

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



}
