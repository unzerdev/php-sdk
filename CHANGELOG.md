# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres
to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.2.1.0](https://github.com/unzerdev/php-sdk/compare/1.2.0.0..1.2.1.0)

### Added

*   Allow fetching payment type config for paylater invoice.
*   Add payment type "Klarna".
*   Provide Example for "Klarna" payment method.
*   Add `language` property to Customer class which is required for klarna payments.

### Changed

*   Update UPL Invoice Example to display the "my consent" link in payment form.
*   Update examples to display shortId on success page also for payments done via payment pages.
*   Add background and logo image URLs to examples, which can be adjusted in the `_enableExamples.php` file.
*   General adjustments of examples:
    *   Ensure all payment forms use correct css class.
    *   Place submit button into an extra div element.
    *   Disable submit button by default if a payment type has mandatory input fields.

### Deprecated

*   The `activateRecurring` method for Card and SepaDirectDebit types is deprecated.
    *   For Card recurring please use `Charge|Authorization::setRecurrenceType` and perform a charge or authorization.
    *   For Sepa Direct Debit a successful charge will automatically set the type resource as recurring.

## [1.2.0.0](https://github.com/unzerdev/php-sdk/compare/1.1.5.0..1.2.0.0)

### Added

*   Add payment type Paylater Invoice.
*   Add properties `companyType` and `owner` to `CompanyInfo` class.
*   Add `shippingType` property to `Address` class.
*   Allow setting the clientIp manually.
*   Allow setting `riskData` for authorize request.
*   Allow setting shipping data for charge request such as `deliveryTrackingId`, `deliveryService` and `returnTrackingId`.
*   Add new methods for `authorize` and `charge` transactions that use prepared objects only:
    *   `Unzer::performAuthorization()`
    *   `Unzer::performCharge()`
    *   `Unzer::performChargeOnPayment()`
*   Add new methods to cancel payments done via paylater-invoice type:
    *   `Unzer::cancelAuthorizedPayment()`
    *   `Unzer::cancelChargedPayment()`
*   Add new methods to fetch cancellations of payment done via paylater-invoice type:
    *   `Unzer::fetchPaymentReversal()`
    *   `Unzer::fetchPaymentRefund()`
*   Add Paylater Invoice example including the function to capture an authorized payment.

### Changed

*   Remove redundant `currency` parameter from `Unzer::chargePayment()` method.
*   Add `geoLocation` property to all payment type classes.
*   Several minor improvements.
*   Add account information coming from PAPI to Authorize class.

### Deprecated

*   Classes:
    *   Mark `InvoiceSecured` as deprecated, will be replaced by `PaylaterInvoice`.
*   Methods:
    *   Mark `Unzer::authorize()` as deprecated. Please use `Unzer::performAuthorization()` instead.
    *   Mark `Unzer::charge()` as deprecated. Please use `Unzer::performCharge()` instead.
    *   Mark `Unzer::chargePayment()` as deprecated. Please use `Unzer::performChargeOnPayment()` instead.
    *   Mark `Unzer::chargeAuthorization()` as deprecated. Please use `Unzer::performChargeOnPayment()` instead.

## [1.1.5.0](https://github.com/unzerdev/php-sdk/compare/1.1.4.2..1.1.5.0)

### Added

*   Add Support for basket `v2` resource.

### Changed

*   Add support for payment state `create` which can occur when using payment pages.
*   Examples:
    *   Use `v2/basket` resource for secured payment methods and payment pages.
    *   Remove broken image-URLs of payment page examples.
*   Several minor improvements.

## [1.1.4.2](https://github.com/unzerdev/php-sdk/compare/1.1.4.1..1.1.4.2)

### Added

*   Enable PHP 8.1 compatibility.

### Changed

* Fix an issue that can cause an exception when fetching a payment that contained a "cancel-authorize" transaction even though the payment has no authorization transaction.
* Update broken documentation links in readme.
* Several minor improvements.

## [1.1.4.1](https://github.com/unzerdev/php-sdk/compare/1.1.4.0..1.1.4.1)

### Added

*   Added Apple Pay example.

### Changed

*   Adjust `cancelAmount` logic to work properly with Invoice Secured payments.
*   Updated jQuery and frameworks used in examples.
*   Fixed failing card tests.
*   Several minor improvements.

## [1.1.4.0](https://github.com/unzerdev/php-sdk/compare/1.1.3.0..1.1.4.0)

### Added

*   Enable recurrence type to be set for `charge`, `authorize` and `activateRecurringPayment` methods.

### Changed

*   Enable recurring examples (card paypal)to trigger subsequent transaction from success page.
*   Enable card recurring example to use recurrence type.
*   Several minor improvements.

## [1.1.3.0](https://github.com/unzerdev/php-sdk/compare/1.1.2.0..1.1.3.0)

### Added

*   Enable PHP 8.0 compatibility.
*   Allow PHPUnit version 8.x and 9.x in composer dev requirements and adjust tests accordingly.
*   Payment Page examples: Add missing customer information that are required for payment with Instalment (address, dob, salutation).

### Changed

*   `\UnzerSDK\Services\HttpService::handleErrors` explicitly casts response code to int, to ensure same behaviour on all PHP versions.
*   Several minor changes.

## [1.1.2.0](https://github.com/unzerdev/php-sdk/compare/1.1.1.1..1.1.2.0)

### Added

*   Introduce the payment type Applepay.

### Changed

*   Examples:
    *   Card Examples - Ensure that error messages are displayed just one time.
    *   Configuration - Change default protocol to https.
    *   Configuration - Correct vendor name of path constant `UNZER_PAPI_FOLDER`.
*   Update documentation links.

## [1.1.1.1](https://github.com/unzerdev/php-sdk/compare/1.1.1.0..1.1.1.1)

### Fix

*   Change debug logging of failed tests that depend on another one to work as expected.
*   PayPal recurring example: Response handling changed to check the recurring status of the payment type.

### Added

*   Extended testing for Instalment payment type.
*   Cards (extended) example using email UI element.

### Changed

*   Remove PhpUnit 8 support.
*   Card recurring example using email UI element.
*   Card example and paypage examples use a dummy customer-email to ensure they work with 3ds2.
*   Several minor changes.

## [1.1.1.0](https://github.com/unzerdev/php-sdk/compare/1.1.0.0..1.1.1.0)

### Changed

*   Add email property to payment type `card` to meet 3Ds2.x regulations.
*   Several minor changes.

## [1.1.0.0](https://github.com/unzerdev/php-sdk/compare/1260b8314af1ac461e33f0cfb382ffcd0e87c105..1.1.0.0)

### Changed

*   Rebranding of the SDK.
*   Removed payment type string from URL when fetching a payment type resource.
*   Replace payment methods guaranteed/factoring by secured payment methods, i.e.:
    *   `InvoiceGuaranteed` and `InvoiceFactoring` replaced by `InvoiceSecured`.
    *   `SepaDirectDebitGuaranteed` replaced by `SepaDirectDebitSecured`.
    *   `HirePurchaseDirectDebit` replaced by `InstallmentSecured`.
    *   Basket is now mandatory for all those payment types above.
*   Added mapping of old payment type ids to the new payment type resources.
*   Constant in `\UnzerSDK\Constants\ApiResponseCodes` got renamed:
    *   `API_ERROR_IVF_REQUIRES_CUSTOMER` renamed to `API_ERROR_FACTORING_REQUIRES_CUSTOMER`.
    *   `API_ERROR_IVF_REQUIRES_BASKET` renamed to `API_ERROR_FACTORING_REQUIRES_BASKET`.
*   Several minor changes.

### Remove

*   Remove deprecated methods:
    *   getAmountTotal
    *   setAmountTotal
    *   getCardHolder
    *   setHolder
    *   cancel
    *   cancelAllCharges
    *   cancelAuthorization
    *   getResource
    *   fetchResource
*   Remove deprecated constants:
    *   API_ERROR_AUTHORIZE_ALREADY_CANCELLED
    *   API_ERROR_CHARGE_ALREADY_CHARGED_BACK
    *   API_ERROR_BASKET_ITEM_IMAGE_INVALID_EXTENSION
    *   ENV_VAR_NAME_DISABLE_TEST_LOGGING