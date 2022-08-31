<?php
/**
 * This file defines the constants needed for the card example.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\examples
 */

require_once __DIR__ . '/_enableExamples.php';
if (defined('UNZER_PAPI_EXAMPLES') && UNZER_PAPI_EXAMPLES !== true) {
    exit();
}

const EXAMPLE_BASE_FOLDER = UNZER_PAPI_URL . UNZER_PAPI_FOLDER;
define('SUCCESS_URL', EXAMPLE_BASE_FOLDER . 'Success.php');
define('PENDING_URL', EXAMPLE_BASE_FOLDER . 'Pending.php');
define('FAILURE_URL', EXAMPLE_BASE_FOLDER . 'Failure.php');
define('RETURN_CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'ReturnController.php');
define('BACKEND_URL', EXAMPLE_BASE_FOLDER . 'Backend/ManagePayment.php');
define('BACKEND_FAILURE_URL', EXAMPLE_BASE_FOLDER . 'Backend/Failure.php');
define('RECURRING_PAYMENT_CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'CardRecurring/RecurringPaymentController.php');
define('CHARGE_PAYMENT_CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'Backend/ChargePaymentController.php');
define('CANCEL_PAYMENT_CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'Backend/CancelPaymentController.php');
