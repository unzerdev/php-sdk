<?php
/**
 * For security reasons all examples are disabled by default
 * You can switch the constant 'HEIDELPAY_PHP_PAYMENT_API_EXAMPLES' to true to make the examples executable.
 * But you should always set it false on productive environments.
 *
 * Copyright (C) 2018 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\examples
 */

/* Set to true if you want to enable the examples */
define('HEIDELPAY_PHP_PAYMENT_API_EXAMPLES', false);

/* Please set this to your url. It must be reachable over the net*/
define('HEIDELPAY_PHP_PAYMENT_API_URL', 'http://'.$_SERVER['HTTP_HOST']);

/* Please enter the path from root directory to the example folder */
define('HEIDELPAY_PHP_PAYMENT_API_FOLDER', '/vendor/heidelpay/heidelpay-php/examples/');

/* Please provide your own sandbox-keypair here. */
define('HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY', 's-priv-***');
define('HEIDELPAY_PHP_PAYMENT_API_PUBLIC_KEY', 's-pub-***');
