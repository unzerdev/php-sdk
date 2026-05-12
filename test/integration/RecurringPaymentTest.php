<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * Test cases to verify functionality and integration of recurring payments.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Constants\RecurrenceTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class RecurringPaymentTest extends BaseIntegrationTest
{
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
        $this->useLegacyKey();
        /** @var SepaDirectDebit $dd */
        $dd = $this->unzer->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $this->assertFalse($dd->isRecurring());
        $this->unzer->performCharge(new Charge(10.0, 'EUR', self::RETURN_URL), $dd);
        $dd = $this->unzer->fetchPaymentType($dd->getId());
        $this->assertTrue($dd->isRecurring());

        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_RECURRING_ALREADY_ACTIVE);
        $this->unzer->activateRecurringPayment($dd, self::RETURN_URL, RecurrenceTypes::SCHEDULED);
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
        $this->unzer->activateRecurringPayment($card, 'https://dev.unzer.com', RecurrenceTypes::ONE_CLICK);
    }
}
