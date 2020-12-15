# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.1.1.0][1.1.1.0]

### Changed
* Add email property to payment type `card` to meet 3Ds2.x regulations.
* Several minor changes.

## [1.1.0.0][1.1.0.0]

### Changed
* Rebranding of the SDK.
* Removed payment type string from URL when fetching a payment type resource.
* Replace payment methods guaranteed/factoring by secured payment methods, i.e.:
    * `InvoiceGuaranteed` and `InvoiceFactoring` replaced by `InvoiceSecured`
    * `SepaDirectDebitGuaranteed` replaced by `SepaDirectDebitSecured`
    * `HirePurchaseDirectDebit` replaced by `InstallmentSecured`
    * Basket is now mandatory for all those payment types above.
* Added mapping of old payment type ids to the new payment type resources.
* Constant in `\UnzerSDK\Constants\ApiResponseCodes` got renamed:
    * `API_ERROR_IVF_REQUIRES_CUSTOMER` renamed to `API_ERROR_FACTORING_REQUIRES_CUSTOMER`.
    * `API_ERROR_IVF_REQUIRES_BASKET` renamed to `API_ERROR_FACTORING_REQUIRES_BASKET`.
* Several minor changes.
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
[1.1.1.0]: https://github.com/unzerdev/php-sdk/compare/1.1.0.0..1.1.1.0
