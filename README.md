[![Codacy Badge](https://api.codacy.com/project/badge/Grade/46f5a3e14f2144fb84d1989dca2a7a5c)](https://www.codacy.com/app/heidelpay/heidelpayPHP?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=heidelpay/heidelpayPHP&amp;utm_campaign=Badge_Grade)

![Logo](https://dev.heidelpay.com/devHeidelpay_400_180.jpg)

# heidelpay php-sdk
This SDK provides for an easy way to connect to the heidelpay Rest API.

Please refer to the following documentation for installation instructions and usage information.

*   [API Documentation](https://docs.heidelpay.com/docs/introduction)
*   [PHP SDK Documentation](https://docs.heidelpay.com/docs/php-sdk)

## Activate the Integration Examples
In order to enable the examples do the following:

1.  Navigate to the examples folder open the file: `_enableExamples.php` and change
`define('HEIDELPAY_PHP_PAYMENT_API_EXAMPLES', FALSE);` to TRUE
Please make sure to switch it off again, after you launch your application.

2.  You may need to adapt the constant `HEIDELPAY_PHP_PAYMENT_API_FOLDER` to match the folder structure of the example.
E.g. `define('HEIDELPAY_PHP_PAYMENT_API_FOLDER', '/projects/heidelpayPHP/vendor/heidelpay/heidelpay-php/examples/');`
