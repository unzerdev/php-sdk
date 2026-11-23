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
        // NOTE: the concrete getCode()/merchant message could not be observed in the dev environment
        // (no sandbox credentials). Confirm the exact code from the first CI run and tighten this
        // assertion (e.g. ->expectExceptionCode(...)) with the observed value.
        $this->expectException(UnzerApiException::class);

        $query = new PaylaterPlansQuery(99.99, 'EUR', 'DE');
        $unsupportedType = new SepaDirectDebit('DE89370400440532013000');
        $this->unzer->fetchPaylaterPlans($unsupportedType, $query);
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
        // NOTE: the concrete getCode()/merchant message per case could not be observed in the dev
        // environment (no sandbox credentials). Confirm the exact code(s) from the first CI run and
        // tighten this assertion (e.g. ->expectExceptionCode(...)) with the observed value(s).
        $this->expectException(UnzerApiException::class);

        $query = new PaylaterPlansQuery($amount, $currency, $country);
        $this->unzer->fetchPaylaterPlans(new PaylaterInvoice(), $query);
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
