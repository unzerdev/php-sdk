<?php
/**
 * This file defines the constants needed for the card example.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/examples
 */

require_once __DIR__ . '/../Constants.php';

define('EXAMPLE_URL', EXAMPLE_BASE_FOLDER . 'CreditCard');
define('CHARGE_CANCEL_CONTROLLER_URL', EXAMPLE_URL . '/ChargeCancelController.php');
define('AUTH_REVERSAL_CONTROLLER_URL', EXAMPLE_URL . '/AuthReversalController.php');
define('AUTH_CONTROLLER_URL', EXAMPLE_URL . '/AuthController.php');
define('CHARGE_CONTROLLER_URL', EXAMPLE_URL . '/ChargeController.php');
