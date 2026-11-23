<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify the generalized paylater /plans endpoint
 * (Unzer::fetchPaylaterPlans) for Paylater Invoice and Paylater Direct Debit (CCD2).
 *
 * The PAPI `types/paylater-invoice/plans` and `types/paylater-direct-debit/plans` endpoints (CC-4014)
 * are now live; these tests run against the sandbox using the repository's standard integration
 * credentials (UNZER_PAPI_TEST_PRIVATE_KEY_DEFAULT), exactly like the existing installment integration
 * test. They are no longer gated.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\CustomerTypes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\PaylaterDirectDebit;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\test\BaseIntegrationTest;

use function count;

class PaylaterPlansTest extends BaseIntegrationTest
{
    /**
     * Verify real plans can be fetched for Paylater Invoice and Direct Debit, exposing a secciUrl.
     *
     * @test
     *
     * @dataProvider supportedPaylaterTypeDataProvider
     */
    public function plansAreFetchableWithSecciUrl(BasePaymentType $paymentType): void
    {
        $query = new PaylaterPlansQuery(99.99, 'EUR', 'DE', CustomerTypes::B2C);
        $plans = $this->unzer->fetchPaylaterPlans($paymentType, $query);

        $this->assertTrue($plans->isSuccess());
        $this->assertGreaterThan(0, count($plans->getPlans()));
        foreach ($plans->getPlans() as $plan) {
            $this->assertNotEmpty($plan->getSecciUrl());
        }
    }

    /**
     * Permissive design: an unsupported payment type is passed through and rejected by PAPI.
     *
     * @test
     */
    public function unsupportedPaymentTypeIsRejectedByApi(): void
    {
        $query = new PaylaterPlansQuery(99.99, 'EUR', 'DE');
        $unsupportedType = new SepaDirectDebit('DE89370400440532013000');

        // Example observed sandbox behaviour (illustrative only, not asserted - codes/messages may change):
        // code API.720.100.001, merchantMessage "The given payment method plans is not found.",
        // customerMessage "Invalid payment method. Please contact us for more information."
        try {
            $this->unzer->fetchPaylaterPlans($unsupportedType, $query);
            $this->fail('Expected ' . UnzerApiException::class . ' was not thrown.');
        } catch (UnzerApiException $e) {
            $this->assertNotEmpty($e->getMerchantMessage());
        }
    }

    /**
     * Invalid input is delegated to PAPI and surfaced as an UnzerApiException.
     *
     * @test
     *
     * @dataProvider invalidQueryDataProvider
     */
    public function invalidInputThrowsUnzerApiException(float $amount, string $currency, string $country): void
    {
        $query = new PaylaterPlansQuery($amount, $currency, $country);

        try {
            $this->unzer->fetchPaylaterPlans(new PaylaterInvoice(), $query);
            $this->fail('Expected ' . UnzerApiException::class . ' was not thrown.');
        } catch (UnzerApiException $e) {
            $this->assertNotEmpty($e->getMerchantMessage());
        }
    }

    //<editor-fold desc="Data Providers">

    public function supportedPaylaterTypeDataProvider(): array
    {
        return [
            'invoice'     => [new PaylaterInvoice()],
            'directDebit' => [new PaylaterDirectDebit('DE89370400440532013000', 'Peter Mustermann')],
        ];
    }

    public function invalidQueryDataProvider(): array
    {
        return [
            'negative amount' => [-99.99, 'EUR', 'DE'],
            'zero amount'     => [0.0, 'EUR', 'DE'],
            'invalid currency' => [99.99, 'XXX', 'DE'],
            'invalid country'  => [99.99, 'EUR', 'ZZ'],
        ];
    }

    //</editor-fold>
}
