<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class verifies function of the generalized paylater plan resources.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Resources;

use UnzerSDK\Constants\CustomerTypes;
use UnzerSDK\Resources\EmbeddedResources\Paylater\InstallmentRate;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlan;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;
use UnzerSDK\Resources\PaylaterInstallmentPlans;
use UnzerSDK\Resources\PaylaterPlans;
use UnzerSDK\test\BasePaymentTest;
use UnzerSDK\test\Fixtures\JsonProvider;

class PaylaterPlansTest extends BasePaymentTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected(): void
    {
        $plans = new PaylaterPlans();

        $this->assertNull($plans->getAmount());
        $this->assertNull($plans->getCurrency());
        $this->assertNull($plans->getCountry());
        $this->assertNull($plans->getCustomerType());

        $plans->setAmount(2.345)
            ->setCurrency('USD')
            ->setCountry('DE')
            ->setCustomerType(CustomerTypes::B2C);

        $this->assertEquals(2.345, $plans->getAmount());
        $this->assertEquals('USD', $plans->getCurrency());
        $this->assertEquals('DE', $plans->getCountry());
        $this->assertEquals(CustomerTypes::B2C, $plans->getCustomerType());
    }

    /**
     * Verify the query string is assembled from the query parameter.
     *
     * @test
     *
     * @dataProvider queryStringDataProvider
     */
    public function resourcePathContainsQueryString(float $amount, string $currency, string $country, string $customerType): void
    {
        $query = new PaylaterPlansQuery($amount, $currency, $country, $customerType);
        $plans = (new PaylaterPlans())->setQueryParameter($query);
        $this->assertEquals("plans?amount={$amount}&country={$country}&currency={$currency}&customerType={$customerType}", $plans->getResourcePath());
    }

    /**
     * The default customer type is B2C when none is provided.
     *
     * @test
     */
    public function defaultCustomerTypeIsB2c(): void
    {
        $query = new PaylaterPlansQuery(100, 'EUR', 'DE');
        $this->assertEquals(CustomerTypes::B2C, $query->getCustomerType());
    }

    /**
     * Verify the Json response gets mapped properly for the supported paylater types.
     *
     * @test
     *
     * @dataProvider responseFixtureDataProvider
     */
    public function jsonResponseIsHandledProperly(string $fixture): void
    {
        $plans = new PaylaterPlans();
        $jsonResponse = json_decode(JsonProvider::getJsonFromFile($fixture));
        $plans->handleResponse($jsonResponse);

        $this->assertEquals($jsonResponse->id, $plans->getId());
        $this->assertEquals($jsonResponse->amount, $plans->getAmount());
        $this->assertEquals($jsonResponse->currency, $plans->getCurrency());
        $this->assertCount(count($jsonResponse->plans), $plans->getPlans());

        $this->comparePlans($jsonResponse->plans, $plans->getPlans());
    }

    /**
     * Plans without installmentRates map cleanly (null rates), no error.
     *
     * @test
     */
    public function planWithoutInstallmentRatesMapsCleanly(): void
    {
        $plans = new PaylaterPlans();
        $plans->handleResponse(json_decode(JsonProvider::getJsonFromFile('paylaterPlansResponseNoRates.json')));

        $this->assertNotEmpty($plans->getPlans());
        foreach ($plans->getPlans() as $plan) {
            $this->assertNotNull($plan->getSecciUrl());
            $this->assertNull($plan->getInstallmentRates());
        }
    }

    /**
     * An empty plans array maps to an empty list.
     *
     * @test
     */
    public function emptyPlansArrayMapsToEmptyList(): void
    {
        $plans = new PaylaterPlans();
        $plans->handleResponse(json_decode(JsonProvider::getJsonFromFile('paylaterPlansResponseEmpty.json')));

        $this->assertIsArray($plans->getPlans());
        $this->assertEmpty($plans->getPlans());
    }

    /**
     * The minimumInstallmentFee sent by the API is exposed on each plan.
     *
     * @test
     */
    public function minimumInstallmentFeeIsMapped(): void
    {
        $plans = new PaylaterPlans();
        $plans->handleResponse(json_decode(JsonProvider::getJsonFromFile('paylaterPlansResponse.json')));

        $this->assertEquals('2.50', $plans->getPlans()[0]->getMinimumInstallmentFee());
    }

    /**
     * An installment rate without a date (as returned by the amount=0 edge case) maps without a warning.
     *
     * @test
     */
    public function installmentRateWithoutDateMapsWithoutWarning(): void
    {
        $response = json_decode(json_encode([
            'id' => 'Tx-zero',
            'amount' => '0.00',
            'currency' => 'EUR',
            'plans' => [[
                'numberOfRates' => 1,
                'totalAmount' => '0.00',
                'secciUrl' => 'https://example.test/secci',
                'installmentRates' => [['rate' => '0.00']],
            ]],
        ]));

        $plans = new PaylaterPlans();
        $plans->handleResponse($response);

        $rate = $plans->getPlans()[0]->getInstallmentRates()[0];
        $this->assertNull($rate->getDate());
        $this->assertEquals('0.00', $rate->getRate());
    }

    /**
     * Every plan parameter is gettable (never throws) for each supported paylater type, returning the
     * mapped value or null for the optional fields a given type does not send.
     *
     * @test
     *
     * @dataProvider responseFixtureDataProvider
     */
    public function allPlanParametersAreGettableForEachType(string $fixture): void
    {
        $plans = new PaylaterPlans();
        $plans->handleResponse(json_decode(JsonProvider::getJsonFromFile($fixture)));

        $this->assertNotEmpty($plans->getPlans());
        foreach ($plans->getPlans() as $plan) {
            $this->assertAllPlanParametersGettable($plan);
        }
    }

    /**
     * A response where optional fields arrive as null is mapped without a TypeError (same pattern as
     * CC-4255/CC-4256); the affected getters return null and the fetch succeeds.
     *
     * @test
     */
    public function nullPlanAndEnvelopeFieldsMapWithoutError(): void
    {
        $response = json_decode(json_encode([
            'id' => 'Tx-null',
            'amount' => null,
            'currency' => 'EUR',
            'plans' => [[
                'numberOfRates' => 1,
                'totalAmount' => '100.00',
                'interestAmount' => null,
                'minimumInstallmentFee' => null,
                'nominalInterestRate' => null,
                'effectiveInterestRate' => null,
                'secciUrl' => null,
                'installmentRates' => null,
            ]],
        ]));

        $plans = new PaylaterPlans();
        $plans->handleResponse($response);

        $this->assertNull($plans->getAmount());
        $plan = $plans->getPlans()[0];
        $this->assertSame(1, $plan->getNumberOfRates());
        $this->assertNull($plan->getSecciUrl());
        $this->assertNull($plan->getInterestAmount());
        $this->assertNull($plan->getMinimumInstallmentFee());
        $this->assertNull($plan->getNominalInterestRate());
        $this->assertNull($plan->getEffectiveInterestRate());
        $this->assertNull($plan->getInstallmentRates());
    }

    /**
     * A null plans array in the response maps to an empty list instead of throwing.
     *
     * @test
     */
    public function nullPlansArrayMapsToEmptyList(): void
    {
        $response = json_decode(json_encode(['id' => 'Tx-null-plans', 'currency' => 'EUR', 'plans' => null]));

        $plans = new PaylaterPlans();
        $plans->handleResponse($response);

        $this->assertIsArray($plans->getPlans());
        $this->assertEmpty($plans->getPlans());
    }

    /**
     * Migration guard: for the installment response, the new PaylaterPlans flow maps to the same plan
     * data as the deprecated PaylaterInstallmentPlans flow. Confirms switching is a rename (plus passing
     * the payment type to fetchPaylaterPlans()) and not a data-shape change.
     *
     * @test
     */
    public function newFlowMapsSameInstallmentDataAsLegacyFlow(): void
    {
        $json = JsonProvider::getJsonFromFile('paylaterPlansResponse.json');

        $legacy = new PaylaterInstallmentPlans();
        $legacy->handleResponse(json_decode($json));
        $new = new PaylaterPlans();
        $new->handleResponse(json_decode($json));

        $this->assertSame($legacy->getId(), $new->getId());
        $this->assertSame($legacy->getAmount(), $new->getAmount());
        $this->assertSame($legacy->getCurrency(), $new->getCurrency());
        $this->assertCount(count($legacy->getPlans()), $new->getPlans());

        foreach ($legacy->getPlans() as $index => $legacyPlan) {
            $newPlan = $new->getPlans()[$index];
            $this->assertSame($legacyPlan->getSecciUrl(), $newPlan->getSecciUrl());
            $this->assertSame($legacyPlan->getNumberOfRates(), $newPlan->getNumberOfRates());
            $this->assertSame($legacyPlan->getTotalAmount(), $newPlan->getTotalAmount());
            $this->assertSame($legacyPlan->getMinimumInstallmentFee(), $newPlan->getMinimumInstallmentFee());
            $this->assertSame($legacyPlan->getNominalInterestRate(), $newPlan->getNominalInterestRate());
            $this->assertSame($legacyPlan->getEffectiveInterestRate(), $newPlan->getEffectiveInterestRate());

            $legacyRates = $legacyPlan->getInstallmentRates() ?? [];
            $newRates = $newPlan->getInstallmentRates() ?? [];
            $this->assertCount(count($legacyRates), $newRates);
            foreach ($legacyRates as $rateIndex => $legacyRate) {
                $this->assertSame($legacyRate->getDate(), $newRates[$rateIndex]->getDate());
                $this->assertSame($legacyRate->getRate(), $newRates[$rateIndex]->getRate());
            }
        }
    }

    //<editor-fold desc="Helper">

    /**
     * Asserts every getter of a plan item is callable and returns the expected nullable type.
     */
    private function assertAllPlanParametersGettable(PaylaterPlan $plan): void
    {
        $this->assertInstanceOf(PaylaterPlan::class, $plan);
        $this->assertIsInt($plan->getNumberOfRates());
        $this->assertIsString($plan->getTotalAmount());
        $this->assertIsString($plan->getMinimumInstallmentFee());
        $this->assertNotEmpty($plan->getSecciUrl());

        $interestAmount = $plan->getInterestAmount();
        $this->assertTrue($interestAmount === null || is_string($interestAmount));
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

    /**
     * @param object[]          $expected
     * @param PaylaterPlan[] $actual
     */
    private function comparePlans(array $expected, array $actual): void
    {
        foreach ($expected as $index => $plan) {
            $this->assertEquals($plan->numberOfRates ?? null, $actual[$index]->getNumberOfRates());
            $this->assertEquals($plan->totalAmount ?? null, $actual[$index]->getTotalAmount());
            $this->assertEquals($plan->interestAmount ?? null, $actual[$index]->getInterestAmount());
            $this->assertEquals($plan->minimumInstallmentFee ?? null, $actual[$index]->getMinimumInstallmentFee());
            $this->assertEquals($plan->nominalInterestRate ?? null, $actual[$index]->getNominalInterestRate());
            $this->assertEquals($plan->effectiveInterestRate ?? null, $actual[$index]->getEffectiveInterestRate());
            $this->assertNotNull($actual[$index]->getSecciUrl());
            $this->assertEquals($plan->secciUrl, $actual[$index]->getSecciUrl());
            $this->compareRates($plan->installmentRates ?? [], $actual[$index]->getInstallmentRates() ?? []);
        }
    }

    /**
     * @param object[]          $expected
     * @param InstallmentRate[] $actual
     */
    private function compareRates(array $expected, array $actual): void
    {
        foreach ($expected as $index => $rate) {
            $this->assertEquals($rate->date ?? null, $actual[$index]->getDate());
            $this->assertEquals($rate->rate ?? null, $actual[$index]->getRate());
        }
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    public function queryStringDataProvider(): array
    {
        return [
            [100, 'EUR', 'DE', 'B2C'],
            [100, 'EUR', 'GB', 'B2B'],
            [100, 'CHF', 'CHE', 'B2C'],
        ];
    }

    public function responseFixtureDataProvider(): array
    {
        return [
            'installment' => ['paylaterPlansResponse.json'],
            'invoice'     => ['paylaterInvoicePlansResponse.json'],
            'directDebit' => ['paylaterDirectDebitPlansResponse.json'],
        ];
    }

    //</editor-fold>
}
