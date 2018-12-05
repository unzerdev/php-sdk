# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.0.0-beta.3][1.0.0.0-beta.3]

### Fix
*   Fixed license information.
*   Fixed package information.
*   Fixed return values for several getters.

### Changed
*   Changed versioning paradigm.
*   Changed documentation.
*   Refactored examples.
*   Refactored HttpAdapter implementation and made custom adapters injectable.
*   Refactored Exceptions.
*   Reduced complexity in general.
*   Refactored expiry date validation.
*   Store transaction date as \DateTime.
*   Enabled some skipped tests.
*   Refactored integration tests.
*   Changed travis configuration to perform unit tests with coverage-analysis instead of integration tests.

### Added
*   Added unit tests.
*   Added debug handler injection.
*   Added Metadata resource.
*   Added fetching customer via (external) customerId.
*   Added private key to Keypair resource.
*   Added PIS payment type.
*   Added ResourceNameService.
*   Added method to create or update customer (uses customerId field to update if the id is not set).
*   Added shipping address to customer.

## [1.0.0-beta.2][1.0.0-beta.2]

### Fix
*   Fix result urls.
*   Fix PhpDoc.

## [1.0.0-beta.1][1.0.0-beta.1]

### Added
*   Beta release for the new php sdk.

[1.0.0-beta.1]: https://github.com/heidelpay/heidelpayPHP/tree/1.0.0-beta.1
[1.0.0-beta.2]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0-beta.1..1.0.0-beta.2
[1.0.0.0-beta.3]: https://github.com/heidelpay/heidelpayPHP/compare/1.0.0-beta.2..1.0.0.0-beta.3
