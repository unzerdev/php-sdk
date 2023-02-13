<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * Test cases to verify functionality and integration of recurring payments.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\integration
 */
namespace UnzerSDK\test\integration;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Constants\RecurrenceTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Unzer;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured;
use UnzerSDK\Services\EnvironmentService;
use UnzerSDK\test\BaseIntegrationTest;
use RuntimeException;

class RecurringPaymentTest extends BaseIntegrationTest
{
    /**
     * Verify exception is thrown if it is called on a non resource object.
     *
     * @test
     */
    public function exceptionShouldBeThrownIfTheObjectIsNotAResource(): void
    {
        $resource = new DummyResource();

        $this->expectException(RuntimeException::class);
        $resource->activateRecurring(self::RETURN_URL);
    }

    /**
     * Verify card with 3ds can activate recurring payments.
     * After recurring call the parameters are set.
     *
     * @test
     *
     * @deprecated since 1.2.1.0 Get removed with `activateRecurring` method.
     */
    public function recurringForCardWith3dsShouldReturnAttributes(): void
    {
        /** @var Card $card */
        $card = $this->unzer->createPaymentType($this->createCardObject()->set3ds(true));
        $recurring = $card->activateRecurring('https://dev.unzer.com', RecurrenceTypes::SCHEDULED);
        $this->assertPending($recurring);
        $this->assertEquals('https://dev.unzer.com', $recurring->getReturnUrl());
        $this->assertEquals('scheduled', $recurring->getRecurrenceType($card));
        $this->assertNotEmpty($recurring->getDate());

        $message = $recurring->getMessage();
        $this->assertEquals(ApiResponseCodes::CORE_TRANSACTION_PENDING, $message->getCode());
        $this->assertNotEmpty($message->getCustomer());
    }

    /**
     * Verify card without 3ds can activate recurring payments.
     *
     * @test
     *
     * @deprecated since 1.2.1.0 Get removed with `activateRecurring` method.
     */
    public function recurringForCardWithout3dsShouldActivateRecurringAtOnce(): void
    {
        $privateKey = EnvironmentService::getTestPrivateKey(true);
        if (empty($privateKey)) {
            $this->markTestIncomplete('No non 3ds private key set');
        }
        $unzer = new Unzer($privateKey);

        $unzer->setDebugMode(true)->setDebugHandler($this->unzer->getDebugHandler());

        /** @var Card $card */
        $card = $unzer->createPaymentType($this->createCardObject()->set3ds(false));
        $this->assertFalse($card->isRecurring());

        $recurring = $card->activateRecurring('https://dev.unzer.com');
        $this->assertSuccess($recurring);

        /** @var Card $fetchedCard */
        $fetchedCard = $unzer->fetchPaymentType($card->getId());
        $this->assertTrue($fetchedCard->isRecurring());
    }

    /**
     * Verify paypal can activate recurring payments.
     *
     * @test
     */
    public function paypalShouldBeAbleToActivateRecurringPayments(): void
    {
        /** @var Paypal $paypal */
        $paypal = $this->unzer->createPaymentType(new Paypal());
        $recurring = $paypal->activateRecurring('https://dev.unzer.com');
        $this->assertPending($recurring);
        $this->assertNotEmpty($recurring->getReturnUrl());
    }

    /**
     * Verify sepa direct debit can activate recurring payments.
     *
     * @test
     */
    public function sepaDirectDebitShouldBeAbleToActivateRecurringPayments(): void
    {
        /** @var SepaDirectDebit $dd */
        $dd = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $this->assertFalse($dd->isRecurring());
        $dd->charge(10.0, 'EUR', self::RETURN_URL);
        $dd = $this->unzer->fetchPaymentType($dd->getId());
        $this->assertTrue($dd->isRecurring());

        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_RECURRING_ALREADY_ACTIVE);
        $this->unzer->activateRecurringPayment($dd, self::RETURN_URL, RecurrenceTypes::SCHEDULED);
    }

    /**
     * Verify sepa direct debit secured can activate recurring payments.
     *
     * @test
     */
    public function sepaDirectDebitSecuredShouldBeAbleToActivateRecurringPayments(): void
    {
        /** @var SepaDirectDebitSecured $ddg */
        $ddg = $this->unzer->createPaymentType(new SepaDirectDebitSecured('DE89370400440532013000'));
        $this->assertFalse($ddg->isRecurring());
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());
        $basket = $this->createBasket();
        $ddg->charge(10.0, 'EUR', self::RETURN_URL, $customer, null, null, $basket);
        $ddg = $this->unzer->fetchPaymentType($ddg->getId());
        $this->assertTrue($ddg->isRecurring());

        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_RECURRING_ALREADY_ACTIVE);
        $this->unzer->activateRecurringPayment($ddg, self::RETURN_URL);
    }

    /**
     * Unsupported recurrence type causes API Exception. 'oneclick' can not be used for recurring request.
     *
     * @test
     */
    public function activateCardRecurringWithOneclickRecurrenceShouldThrowApiException(): void
    {
        /** @var Card $card */
        $card = $this->unzer->createPaymentType($this->createCardObject()->set3ds(true));
        $this->expectException(UnzerApiException::class);
        $card->activateRecurring('https://dev.unzer.com', RecurrenceTypes::ONE_CLICK);
    }
}
