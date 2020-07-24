# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.2.8.0][1.2.8.0]

### Add
*   Curl error string to debug log.
*   Environment variable to enable/disable verbose curl output.
*   Method to get the initial transaction from a payment.
*   Parameter `css` to `Paypage` to enable custom styling. Special thanks to github user [bleenders](https://github.com/bleenders).

### Change
*   Mask auth header in debug log.
*   Refactor test logging.
*   Replace test environment variable.
*   Apply minor changes.
*   Refactor tests due to disabled default keypair.
*   Refactor all files to reflect PHP 7.1 standards.
*   Replace `codacy/coverage` with `codacy/codacy-coverage-reporter`.
*   Update examples.

### Fix
*   Change TravisCI config to use default linux distribution in VM to fix certificate errors during integration tests.
*   Remove invalid PHPUnit config parameters.

### Remove
*   Disabled key pair from examples and tests.
*   Remove deprecated method `authorizeWithPayment`.
*   Remove return value from `deleteCustomer` method.

## [1.2.7.2][1.2.7.2]

### Fix
*   Rename Cards property holder to cardHolder.
*   Add cancellation object to authorization only if cancellation has been successful.

### Change
*   Fix some minor issues.
*   Refactor tests due to API changes.
*   Update response codes due to API changes.

### Added
*   TraceId property to payment and transactions.
*   Email form field to PayPal example.
*   Description to zgReferenceId setter/getter.
*   Added geolocation to Card resource.
*   Payout webhook events.

### Changed
*   Fixed some minor issues.

## [1.2.7.1][1.2.7.1]

### Added
*   PHP version to HTTP header.

### Remove
*   PHP7.0 support.
*   Travis tests using composer update.

### Fix
*   Changed date format string to support times with 24h-format.

## [1.2.7.0][1.2.7.0]

### Added
*   Http headers to debug log.
*   PID to debug log.
*   PHP7.4 support.
*   Handles for automatic testing to examples.

### Changed
*   Update example implementation for Hire Purchase direct debit payment type (FlexiPay® Rate).
*   Write log messages from examples to examples\log\example.log instead of error.log.
*   Several minor changes.

### Fix
*   Ensure locale strings comply to standard.

## [1.2.6.0][1.2.6.0]

### Added
*   Example for Payment Type `Przelewy24`.
*   Enabled recurring payment for SEPA direct debit (guaranteed).

### Fixed
*   Composer: PHP version constraint.
*   Several minor issues.

### Changed
*   Refactor travis config.
*   Refactor heidelpay facade to implement service interfaces.
*   Move payment cancel methods to cancel service. 
*   Cleanup doc comments.
*   Refactor unit tests.
*   Refactor access modifiers of setters intended for system access only.

### Remove
*   Move method doc blocks to service interfaces.
*   Remove dead code.

## [1.2.5.1][1.2.5.1]

### Fix
*   A bug which led to an error when trying to cancel the initial transaction of a charged invoice.

## [1.2.5.0][1.2.5.0]

### Added
*   Hire purchase direct debit payment type (FlexiPay® Rate) including example implementation.
*   Additional parameters from payment type to transactions.
*   Facade method to update payment type.
*   Property `invoiceId` to `Payment` class.
*   Added card details to `Card` payment type.
*   Property `card3ds` to `Paypage`.
*   Geolocation to `Customer` resource.
*   Property `effectiveInterestRate` to `PayPage` to enable `HirePurchase` payment types.

### Changed
*   Refactor invoice guaranteed tests to make them independent from each other.
*   Response Code for basket item image extension error has been deprecated since it has been removed from the API.
*   Refactored cancellation feature to support FlexiPay® Rate cancel.
*   Remove trailing slash from routes.
*   Float values are now rounded with 4 decimal places when exposed to the API. The value of the local object will be updated as well.

### Removed
*   Unused Constants.
*   Obsolete/dead code.

### Fixed
*   Several minor issues.

## [1.2.4.0][1.2.4.0]

### Added
*   It is now possible to exclude payment types from the Paypage.

## [1.2.3.0][1.2.3.0]

### Added
*   An example for `prepayment` payment method.
*   An example for `invoice` payment method.
*   Charge methods `getCancelledAmount` and `getTotalAmount`.
*   Authorize method `getCancelledAmount`.
*   Detailed `keypair` fetch.
*   Added properties to keypair resource.

### Fixed
*   A problem with HeidelpayApiException.
*   A problem which resulted in an error when trying to create a `customer` implicitly with a transaction when its `customerId` was set. 

### Changed
*   Replaced unreliable `Payment::cancel()` method with `Payment::cancelAmount()` which takes multiple cancellation scenarios into account.
*   Replaced `ApiResponseCodes::API_ERROR_AUTHORIZE_ALREADY_CANCELLED` with `ApiResponseCodes::API_ERROR_ALREADY_CANCELLED`.
*   Replaced `ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK` with `ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK`.
*   Add deprecation notice for `Payment::cancelAllCharges` and `Payment::cancelAuthorization`
*   Adapted integration tests with basket to changes in API.
*   Refactor deprecation notices.
*   Refactored and extended unit tests.
*   Test keypair can now be set via environment variables.
*   Activate recurring payment for `SEPA Direct Debit (guaranteed)`.

## [1.2.2.0][1.2.2.0]

### Fixed
*   An issue resulting in an error when a B2B customer is fetched which does not have a function set.

### Added
*   Example to fetch all registered webhooks for the given key pair.
*   Missing API response code for insurance already activated message after shipment.
*   Giropay example
*   Optional parameter orderId to ship call.

### Changed
*   Removed deletion of all webhooks prior to registering to webhook events.

## [1.2.1.0][1.2.1.0]

### Added
*   Parameters `paymentReference` and `invoiceId` to `Authorization` and `Payout`.
*   The SDK now supports the webhook event `payout`.
*   Example for Flexipay direct.
*   Email parameter to `Paypal` payment type.
*   Error id to `HeidelpayApiException`.

### Changed
*   The webhook tests now cover all supported events.

### Removed
*   SAQ-A test due to lack of a corresponding key.

## [1.2.0.0][1.2.0.0]

### This update contains breaking changes and you might have to update your implementation

### Changed
*   Refactored all examples.
*   Fixed iDeal example.
*   Metadata are no longer automatically created and sent when they are not set by the merchant.
*   Version and Type of the SDK are now sent to the API with every request.
*   Add missing feature to readme.
*   Renamed property `Basket::amountTotal` to `Basket::amountTotalGross` to follow the change in the API.
*   Refactor tests.
*   Set `Paypage` default action to 'charge' and restrict values to 'charge' and 'authorize'.

### Added
*   Property `type` to `BasketItem`.
*   An exception is now thrown in case of a `CURL` timeout.
*   The `CURL` timeout can now be changed via environment variable.

### Removed
*   Unnecessary resource setters in `Paypage`.

### Fixed
*   Unit tests work now even in `development` or `staging` environment.

## [1.1.6.0][1.1.6.0]

### Added
*   Support for hosted and embedded payment page.

### Change
*   Refactor integration tests to reduce redundancies and complexity.
*   Enable debug logging with unescaped slashes.

## [1.1.5.0][1.1.5.0]

### Added
*   Add payout transaction for Card and Sepa direct debit (guaranteed) payment type.
*   `Customer` objects can now be created via `CustomerFactory`.

### Fixed
*   Links to documentation page.

### Change
*   Add deprecation notice for `Customer::__construct()` which is replaced by the `CustomerFactory`.

## [1.1.4.0][1.1.4.0]

### Added
*   Enabled switching MGW environment (Prod, Staging, Dev) via environment variable.
*   Examples for iDeal and Sofort.
*   Added PHP 7.3 support.
*   Add recurring payment for PayPal and Card payment type incl. examples.

### Changed
*   Updated unit tests.
*   Refactored examples.
*   Allow to pass the event payload manually to `Heidelpay::fetchResourceFromEvent`.
*   Add properties `imageUrl` and `subTitle` to `BasketItem` resource.

## [1.1.3.0][1.1.3.0]

### Added
*   Added property `paymentReference` to charge and refund transaction.

### Changed
*   Adapted tests to new api behavior.

## [1.1.2.0][1.1.2.0]

### Added
*   Added 3ds flag to card payment type.
*   Added example for invoice guaranteed payment type.
*   Added example for PayPal payment type.
*   Added example for the sepa direct debit guaranteed payment type.
*   Added example for the deletion of all webhooks.

### Fixed
*   Added missing parameter `invoiceId` to `payment::ship` method.
*   A problem which resulted in an error when the property BasketItem::AmountPerUnit is set to 0.0.

### Changed
*   Refactored implementation examples.
*   Enabled fetching the payment via orderId if its id is not set.
*   Changed the default values of Basket and BasketItem.
*   Refactored updating resource properties.
*   Adapt to small API changes.

## [1.1.1.0][1.1.1.0]

### Added
*   Added Webhook(s) functionality: Resource, tests, example.
*   Extended ResourceService with method to fetch a resource by its url.
*   Added method to fetch resource by received event.
*   Added log method to write to custom debuglog to heidelpay facade.
*   Added IdService to fetch ids and types from id strings.
*   Added Alipay payment type and example.
*   Added WeChat Pay payment type and example.
*   Added Invoice Factoring payment type and example.

### Changed
*   Applied several micro optimizations.
*   Added response code to rest call logs.
*   Adapted integration tests to new api version.
*   Added parameter reasonCode to cancel method.
*   Update of ApiResponseCodes.
*   Add optional property invoiceId to charge transaction.

### Fixed
*   Refactor setting basket items in basket resource to avoid problem with missing basket item reference id.

## [1.1.0.0][1.1.0.0]

### This update contains breaking changes and you might have to update your implementation

### Changed
*   Payment types `Prepayment`, `Invoice` and `Invoice guaranteed` can no longer perform the authorize transaction but only direct charge.
*   Enabled chargePayment with payment id.
*   Renamed `KeyValidator` class to `PrivateKeyValidator`.
*   Enabled setting the language for client messages.
*   Merged examples into and added error messages to the failure page.
*   Changed tests to meet updates in payment amount calculation within the payment API.

### Added
*   Basic `PublicKeyValidator` which makes sure the key has the correct format.
*   Basket field `amountTotalVat`.
*   Optional parameter card3ds to charge and authorize transactions to enable switching between 3ds and non-3ds.
*   Transaction message property holding the last code and message from the api.

### Removed
*   Constructor from `AbstractHeidelpayResource`.

### Fixed
*   Several code style issues.

## [1.0.2.0][1.0.2.0]

### Added
*   Made status information available in transactions (isSuccess, isError, isPending).

### Fixed
*   Http-Request: Remove payload from DELETE call.
*   DebugLog: Remove payload output from GET and DELETE calls.

### Changed
*   Several code style issues.

## [1.0.1.0][1.0.1.0]

### Added
*   EPS payment type incl. example code.
*   It is now possible to create, update and fetch a basket as well as referencing it by a authorization or charge.
*   Missing tests for metadata resource.

### Changed
*   Refactor value update to allow for empty strings.
*   Ensuring that transferred floats are always encoded as floats on json-encode.
*   Properties stored in an array are now (json-)encoded as \stdClass not as array.

### Fixed
*   Comments and styles.

## [1.0.0.1][1.0.0.1]

### Fixed
*   Fixed a bug which resulted in an error on getOrderId when the order id has not been set.
*   Fixed namespace configuration in composer.json.
*   Fixed a bug which resulted in metadata not being referenced by charge transactions.

### Changed
*   Set error code to string in HeidelpayApiException by default.
*   Disabled pretty print of json string in response.
*   Re-enabled skipped test.

### Added
*   Examples: Added shortId of the transaction to the success and failure pages.
*   Examples: Added an example debug handler.
*   Readme: Added list of supported payment types.
*   Added missing ApiResponseCodes.
*   Added additional getter to fetch the merchant message from a HeidelpayApiException.
*   Extended integration tests to test with matching addresses and with unmatching addresses.
*   Added additional badges to readme file.

## [1.0.0.0][1.0.0.0]

### Fixed
*   Fixed license information.
*   Fixed package information.
*   Fixed return values for several getters.

### Changed
*   Changed versioning paradigm.
*   Update documentation.
*   Refactored examples.
*   Refactored HttpAdapter implementation and made custom adapters injectable.
*   Refactored exceptions.
*   Reduced complexity in general.
*   Refactored expiry date validation.
*   Store transaction date as \DateTime instead of string.
*   Enabled some skipped tests.
*   Refactored integration tests.
*   Changed travis configuration to perform unit tests with coverage-analysis instead of integration tests.
*   Changed namespaces and packages.

### Added
*   Added unit tests.
*   Added debug handler injection.
*   Added Metadata resource.
*   Added fetching customer via (external) customerId.
*   Added private key to Keypair resource.
*   Added PIS payment type.
*   Added ResourceNameService.
*   Added method to create or update customer (uses customerId field to update if the id is not set).
*   Added shipping address to customer resource.
*   Added support info to readme file.
*   Removed supported currencies and locales.

## [1.0.0-beta.2][1.0.0-beta.2]

### Fixed
*   Fix result urls.
*   Fix PhpDoc.

## [1.0.0-beta.1][1.0.0-beta.1]

### Added
*   Beta release for the new php sdk.

[1.0.0-beta.1]: https://github.com/heidelpay/heidelpayPHP/tree/1.0.0-beta.1
[1.0.0-beta.2]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0-beta.1..1.0.0-beta.2
[1.0.0.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0-beta.2..1.0.0.0
[1.0.0.1]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0.0..1.0.0.1
[1.0.1.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0.1..1.0.1.0
[1.0.2.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.1.0..1.0.2.0
[1.1.0.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.2.0..1.1.0.0
[1.1.1.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.0.0..1.1.1.0
[1.1.2.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.1.0..1.1.2.0
[1.1.3.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.2.0..1.1.3.0
[1.1.4.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.3.0..1.1.4.0
[1.1.5.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.4.0..1.1.5.0
[1.1.6.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.5.0..1.1.6.0
[1.2.0.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.1.6.0..1.2.0.0
[1.2.1.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.0.0..1.2.1.0
[1.2.2.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.1.0..1.2.2.0
[1.2.3.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.2.0..1.2.3.0
[1.2.4.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.3.0..1.2.4.0
[1.2.5.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.4.0..1.2.5.0
[1.2.5.1]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.5.0..1.2.5.1
[1.2.6.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.5.1..1.2.6.0
[1.2.7.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.6.0..1.2.7.0
[1.2.7.1]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.7.0..1.2.7.1
[1.2.7.2]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.7.1..1.2.7.2
[1.2.8.0]: https://github.com/heidelpay/heidelpayPHP/compare/1.2.7.2..1.2.8.0
