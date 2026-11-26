<?php
/**
 * Example: fetch the available Paylater plans — including the CCD2 SECCI document link (`secciUrl`) —
 * for any paylater payment type via `Unzer::fetchPaylaterPlans()`.
 *
 * This is the generalized plan-fetch flow introduced in php-sdk 4.1.0. It replaces the
 * installment-only `Unzer::fetchPaylaterInstallmentPlans()` (see the migration note at the bottom).
 *
 * @link  https://docs.unzer.com/ccd2-2fa/consumer-credit-information-in-checkout/#what-to-do-unzer-only-integration-papi
 * @link  https://docs.unzer.com/
 *
 */

/** Require the example configuration (defines UNZER_PAPI_PRIVATE_KEY and the example on/off switch). */
require_once __DIR__ . '/../_enableExamples.php';
if (defined('UNZER_PAPI_EXAMPLES') && UNZER_PAPI_EXAMPLES !== true) {
    exit();
}

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file. */
require_once __DIR__ . '/../../../../autoload.php';

use UnzerSDK\Constants\CustomerTypes;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;
use UnzerSDK\Resources\PaymentTypes\PaylaterDirectDebit;
use UnzerSDK\Resources\PaymentTypes\PaylaterInstallment;
use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\Unzer;

// Create an Unzer object using your private key and register a debug handler if you want to.
$unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
$unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

// The SECCI PDF is rendered in the client locale: the language follows the locale set here, which the
// SDK sends as the `Accept-Language` header. Omit to use the account default.
$unzer->setLocale('de-DE');

// Query parameters: amount, currency, country and customerType. customerType defaults to B2C.
$query = new PaylaterPlansQuery(119.00, 'EUR', 'DE', CustomerTypes::B2C);

// `fetchPaylaterPlans()` works for every paylater type — pass the payment type you offer.
$paymentTypes = [
    'Paylater Invoice'      => new PaylaterInvoice(),
    'Paylater Direct Debit' => new PaylaterDirectDebit(),
    'Paylater Installment'  => new PaylaterInstallment(),
];

foreach ($paymentTypes as $label => $paymentType) {
    echo "== {$label} ==" . PHP_EOL;

    try {
        $plans = $unzer->fetchPaylaterPlans($paymentType, $query);

        foreach ($plans->getPlans() as $plan) {
            // `secciUrl` links to the Standard European Consumer Credit Information (SECCI) PDF that a
            // CCD2 checkout must offer for download before payment confirmation.
            echo '  ' . $plan->getNumberOfRates() . ' rate(s), total ' . $plan->getTotalAmount()
                . ' — SECCI: ' . $plan->getSecciUrl() . PHP_EOL;
        }
    } catch (UnzerApiException $e) {
        // The SDK does not validate the payment type or the query values; PAPI rejects an unsupported
        // type or an invalid value and the SDK surfaces it as an UnzerApiException.
        echo '  ' . $e->getMerchantMessage() . PHP_EOL;
    }
}

/*
 * Migration — the installment-only flow is deprecated since 4.1.0:
 *
 *   // Deprecated: Unzer::fetchPaylaterInstallmentPlans(InstallmentPlansQuery): PaylaterInstallmentPlans
 *   $plans = $unzer->fetchPaylaterInstallmentPlans(
 *       new UnzerSDK\Resources\EmbeddedResources\Paylater\InstallmentPlansQuery(119.00, 'EUR', 'DE')
 *   );
 *
 *   // Replacement: same query fields, but pass the payment type to the generalized method.
 *   $plans = $unzer->fetchPaylaterPlans(
 *       new PaylaterInstallment(),
 *       new PaylaterPlansQuery(119.00, 'EUR', 'DE')
 *   );
 *
 * The legacy `PaylaterInstallmentPlans` / `InstallmentPlansQuery` classes keep their original public
 * signatures and still work, but should be replaced with `PaylaterPlans` / `PaylaterPlansQuery`.
 */
