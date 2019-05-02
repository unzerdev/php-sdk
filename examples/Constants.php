<?php
/**
 * This file defines the constants needed for the card example.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/examples
 */

require_once __DIR__ . '/_enableExamples.php';
if (defined('HEIDELPAY_PHP_PAYMENT_API_EXAMPLES') && HEIDELPAY_PHP_PAYMENT_API_EXAMPLES !== true) {
    exit();
}

const EXAMPLE_BASE_FOLDER = HEIDELPAY_PHP_PAYMENT_API_URL . HEIDELPAY_PHP_PAYMENT_API_FOLDER;
define('SUCCESS_URL', EXAMPLE_BASE_FOLDER . 'Success.php');
define('FAILURE_URL', EXAMPLE_BASE_FOLDER . 'Failure.php');
define('WEBHOOK_CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'Webhooks/WebhookController.php');
define('DEFAULT_PRIVATE_KEY', 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');
define('DEFAULT_PUBLIC_KEY', 's-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa');
