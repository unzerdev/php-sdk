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
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use Exception;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\CustomerFactory;
use heidelpayPHP\Resources\EmbeddedResources\Address;
use heidelpayPHP\Resources\InstalmentPlan;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;
use function count;

class HirePurchaseDirectDebitTest extends BasePaymentTest
{
    /**
     * Verify the following features:
     * 1. fetching instalment plans.
     * 2. selecting plan
     * 3. create hp resource
     * 4. fetch hp resource
     * 5 test update hp resource
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function instalmentPlanShouldBeSelectable()
    {
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[1];

        /** @var HirePurchaseDirectDebit $hdd */
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann');
        $this->heidelpay->createPaymentType($hdd);
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());

        $fetchedHdd = $this->heidelpay->fetchPaymentType($hdd->getId());
        $this->assertEquals($hdd->expose(), $fetchedHdd->expose());

        $hdd->setIban('DE89370400440532013000')
            ->setBic('COBADEFFXXX')
            ->setAccountHolder('Peter Universum')
            ->setInvoiceDate($this->getYesterdaysTimestamp())
            ->setInvoiceDueDate($this->getTomorrowsTimestamp());
        $hddClone = clone $hdd;
        $this->heidelpay->updatePaymentType($hdd);
        $this->assertEquals($hddClone->expose(), $hdd->expose());
    }

    /**
     * Verify Hire Purchase direct debit authorization (positive and negative).
     *
     * @test
     * @dataProvider CustomerRankingDataProvider
     *
     * @param $firstname
     * @param $lastname
     * @param $errorCode
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function hirePurchaseDirectDebitAuthorize($firstname, $lastname, $errorCode)
    {
        $hpPlans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99);
        $selectedPlan = $hpPlans->getPlans()[0];
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann');
        $this->heidelpay->createPaymentType($hdd);

        $customer = $this->getCustomer()->setFirstname($firstname)->setLastname($lastname);
        $basket = $this->createBasket();

        try {
            $authorize = $hdd->authorize(119.0, 'EUR', self::RETURN_URL, $customer, null, null, $basket);
            if ($errorCode!== null) {
                $this->assertTrue(false, 'Expected error for negative ranking test.');
            }
            $this->assertNotEmpty($authorize->getId());
        } catch (HeidelpayApiException $e) {
            if ($errorCode !== null) {
                $this->assertEquals($errorCode, $e->getCode());
            } else {
                $this->assertTrue(false, "No error expected for positive ranking test. ({$e->getCode()})");
            }
        }
    }

    /**
     * Verify fetching instalment plans.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function instalmentPlanSelectionWithAllFieldsSet()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[0];
        $this->assertCount($selectedPlan->getNumberOfRates(), $selectedPlan->getInstallmentRates(), 'The number of rates should equal the actual rate count.');
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX', $yesterday, $this->getTomorrowsTimestamp());
        $this->heidelpay->createPaymentType($hdd);
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());
    }

    /**
     * Verify charge.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function verifyChargingAnInitializedHirePurchase()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[0];
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX', $yesterday, $this->getTomorrowsTimestamp());
        $this->heidelpay->createPaymentType($hdd);

        $authorize = $hdd->authorize(119.0, 'EUR', self::RETURN_URL, $this->getCustomer(), null, null, $basket = $this->createBasket());
        $payment = $authorize->getPayment();
        $charge = $payment->charge();
        $this->assertNotNull($charge->getId());
    }

    //<editor-fold desc="Shipment">

    /**
     * Verify charge and ship.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function verifyShippingAChargedHirePurchase()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99, $yesterday);

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[0];
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE89370400440532013000', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX', $this->getTodaysDateString(), $this->getTomorrowsTimestamp());
        $this->heidelpay->createPaymentType($hdd);

        $authorize = $hdd->authorize(119.0, 'EUR', self::RETURN_URL, $this->getCustomer(), null, null, $this->createBasket());
        $payment = $authorize->getPayment();
        $payment->charge();
        $shipment = $payment->ship();
        $this->assertNotNull($shipment->getId());
    }

    //</editor-fold>

    //<editor-fold desc="Charge cancel">

    /**
     * Verify full cancel of charged HP.
     *
     * @test
     *
     * @depends verifyChargingAnInitializedHirePurchase
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     *
     * @group skip
     */
    public function verifyChargeAndFullCancelAnInitializedHirePurchase()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[0];
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX', $yesterday, $this->getTomorrowsTimestamp());
        $this->heidelpay->createPaymentType($hdd);

        $authorize = $hdd->authorize(119.0, 'EUR', self::RETURN_URL, $this->getCustomer(), null, null, $basket = $this->createBasket());
        $payment = $authorize->getPayment();
        $payment->charge();
        $cancel = $payment->cancelAmount();
        $this->assertGreaterThan(0, count($cancel));
    }

    /**
     * Verify full cancel of charged HP.
     *
     * @test
     *
     * @depends verifyChargingAnInitializedHirePurchase
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function verifyPartlyCancelChargedHirePurchase()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(119.0, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[0];
        $hdd = new HirePurchaseDirectDebit($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX', $yesterday, $this->getTomorrowsTimestamp());
        $this->heidelpay->createPaymentType($hdd);

        $authorize = $hdd->authorize(119.0, 'EUR', self::RETURN_URL, $this->getCustomer(), null, null, $basket = $this->createBasket());
        $payment = $authorize->getPayment();
        $payment->charge();
        $cancel = $payment->cancelAmount(59.5, null, null, 50.0, 9.5);
        $this->assertCount(1, $cancel);
        $this->assertTrue($payment->isCompleted());
    }

    //</editor-fold>

    //<editor-fold desc="Helper">

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        $customer = CustomerFactory::createCustomer('Manuel', 'Weißmann');
        $address = (new Address())
            ->setStreet('Hugo-Junckers-Straße 3')
            ->setState('DE-BO')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE');
        $customer
            ->setBillingAddress($address)
            ->setBirthDate('2000-12-12')
            ->setEmail('manuel-weissmann@heidelpay.com');

        return $customer;
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function CustomerRankingDataProvider(): array
    {
        return [
            'positive' => ['Manuel', 'Weißmann', null],
            'negative #1 - Payment guarantee' => ['Manuel', 'Zeißmann', ApiResponseCodes::SDM_ERROR_CURRENT_INSURANCE_EVENT],
            'positive #2 - Limit exceeded' => ['Manuel', 'Leißmann', ApiResponseCodes::SDM_ERROR_LIMIT_EXCEEDED],
            'positive #3 - Negative trait' => ['Imuel', 'Seißmann', ApiResponseCodes::SDM_ERROR_NEGATIVE_TRAIT_FOUND],
            'positive #4 - Negative increased risk' => ['Jamuel', 'Seißmann', ApiResponseCodes::SDM_ERROR_INCREASED_RISK]
        ];
    }

    //</editor-fold>
}
