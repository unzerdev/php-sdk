# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.1.0.0][1.1.0.0]

### Added
* Release for rebranded SDK.

### Changed
* Resource path when fetching payment types changed. Removed type name from path.
* Replaced payment Classes:
    * `InvoiceGuaranteed` and `InvoiceFactoring` replaced by `InvoiceSecured`
    * `SepaDirectDebitGuaranteed` replaced by `SepaDirectDebitSecured`
    * `HirePurchaseDirectDebit` replaced by `InstallmentSecured`
    * Basket is now mandatory for those payment types above.

### Remove
* Remove deprecated methods:
    * getAmountTotal
    * setAmountTotal
    * getCardHolder
    * setHolder
    * cancel
    * cancelAllCharges
    * cancelAuthorization
    * getResource
    * fetchResource
* Remove deprecated constants:
    * API_ERROR_AUTHORIZE_ALREADY_CANCELLED
    * API_ERROR_CHARGE_ALREADY_CHARGED_BACK
    * API_ERROR_BASKET_ITEM_IMAGE_INVALID_EXTENSION
    * ENV_VAR_NAME_DISABLE_TEST_LOGGING

[1.1.0.0]: https://github.com/unzerdev/php-sdk/compare/1260b8314af1ac461e33f0cfb382ffcd0e87c105..1.1.0.0