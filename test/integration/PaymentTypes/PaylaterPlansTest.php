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
use UnzerSDK\Resources\EmbeddedResources\Paylater\InstallmentPlan;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\PaylaterDirectDebit;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\test\BaseIntegrationTest;

use function count;
use function is_array;
use function is_float;

class PaylaterPlansTest extends BaseIntegrationTest
{
    /**
     * Verify real plans can be fetched via the generalized endpoint for all three supported paylater
     * types, and that every plan parameter is gettable from the response (null for the optional fields
     * a given type does not send) rather than throwing.
     *
     * @test
     *
     * @dataProvider supportedPaylaterTypeDataProvider
     */
    public function plansAreFetchableWithAllParametersGettable(BasePaymentType $paymentType): void
    {
        $query = new PaylaterPlansQuery(99.99, 'EUR', 'DE', CustomerTypes::B2C);
        $plans = $this->unzer->fetchPaylaterPlans($paymentType, $query);

        $this->assertTrue($plans->isSuccess());
        $this->assertGreaterThan(0, count($plans->getPlans()));
        foreach ($plans->getPlans() as $plan) {
            $this->assertNotEmpty($plan->getSecciUrl());
            $this->assertAllPlanParametersGettable($plan);
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

    //<editor-fold desc="Helper">

    /**
     * Asserts every plan parameter is gettable from the live response: the fields all three types send
     * are present, and the installment-only interest rates read back as float or null without throwing.
     */
    private function assertAllPlanParametersGettable(InstallmentPlan $plan): void
    {
        $this->assertIsInt($plan->getNumberOfRates());
        $this->assertIsString($plan->getTotalAmount());
        $this->assertIsString($plan->getInterestAmount());
        $this->assertIsString($plan->getMinimumInstallmentFee());
        $this->assertNotEmpty($plan->getSecciUrl());

        $nominal = $plan->getNominalInterestRate();
        $this->assertTrue($nominal === null || is_float($nominal));
        $effective = $plan->getEffectiveInterestRate();
        $this->assertTrue($effective === null || is_float($effective));

        $rates = $plan->getInstallmentRates();
        $this->assertTrue($rates === null || is_array($rates));
        foreach ($rates ?? [] as $rate) {
            $this->assertNotNull($rate->getRate());
            $this->assertTrue($rate->getDate() === null || is_string($rate->getDate()));
        }
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    public function supportedPaylaterTypeDataProvider(): array
    {
        return [
            'installment' => [new PaylaterInstallment()],
            'invoice'     => [new PaylaterInvoice()],
            'directDebit' => [new PaylaterDirectDebit('DE89370400440532013000', 'Peter Mustermann')],
        ];
    }

    public function invalidQueryDataProvider(): array
    {
        return [
            // amount=0 is NOT included: the live API accepts it (HTTP 200 with a degenerate plan).
            'negative amount' => [-99.99, 'EUR', 'DE'],
            'invalid currency' => [99.99, 'XXX', 'DE'],
            'invalid country'  => [99.99, 'EUR', 'ZZ'],
        ];
    }

    //</editor-fold>
}
