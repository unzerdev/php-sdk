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
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\CustomerFactory;
use heidelpayPHP\Resources\EmbeddedResources\Address;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\InstalmentPlan;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;

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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function instalmentPlanShouldBeSelectable()
    {
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[1];

        /** @var HirePurchaseDirectDebit $hdd */
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann');
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());

        $fetchedHdd = $this->heidelpay->fetchPaymentType($hdd->getId());
        $this->assertEquals($hdd->expose(), $fetchedHdd->expose());

        $hdd->setIban('DE89370400440532013000')
            ->setBic('COBADEFFXXX')
            ->setInvoiceDate($this->getYesterdaysTimestamp())
            ->setInvoiceDueDate($this->getTomorrowsTimestamp());
        $updatedHdd = $this->heidelpay->updatePaymentType($hdd);
        $this->assertEquals($hdd->expose(), $updatedHdd->expose());
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
     * @throws AssertionFailedError
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function hirePurchaseDirectDebitAuthorize($firstname, $lastname, $errorCode)
    {
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99);
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($plans->getPlans()[1], 'DE46940594210000012345', 'Manuel Weißmann');

        $customer = $this->getCustomer()->setFirstname($firstname)->setLastname($lastname);
        $basket = $this->createBasket();

        try {
            $authorize = $hdd->authorize(123.4, 'EUR', self::RETURN_URL, $customer, null, null, $basket);
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

    //</editor-fold>

    /**
     * Verify fetching instalment plans.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function instalmentPlanSelectionWithAllFieldsSet()
    {
        $yesterday = $this->getYesterdaysTimestamp();
        $plans = $this->heidelpay->fetchDirectDebitInstalmentPlans(123.40, 'EUR', 4.99, $yesterday);
        $this->assertGreaterThan(0, count($plans->getPlans()));

        /** @var InstalmentPlan $selectedPlan */
        $selectedPlan = $plans->getPlans()[1];
        $hdd = $this->heidelpay->selectDirectDebitInstalmentPlan($selectedPlan, 'DE46940594210000012345', 'Manuel Weißmann', $yesterday, 'COBADEFFXXX');
        $this->assertArraySubset($selectedPlan->expose(), $hdd->expose());
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

    /**
     * @return DateTime
     *
     * @throws Exception
     */
    private function getYesterdaysTimestamp(): DateTime
    {
        return (new DateTime())->add(DateInterval::createFromDateString('yesterday'));
    }

    /**
     * @return DateTime
     *
     * @throws Exception
     */
    private function getTomorrowsTimestamp(): DateTime
    {
        return (new DateTime())->add(DateInterval::createFromDateString('tomorrow'));
    }
}
