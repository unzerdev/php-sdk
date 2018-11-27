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
 * @package  heidelpay/mgw_sdk/examples
 */

require_once __DIR__ . '/../Constants.php';

define('EXAMPLE_PATH', __DIR__);
define('EXAMPLE_URL', EXAMPLE_BASE_FOLDER . 'CreditCardWith3D');
define('CHARGE_CANCEL_CONTROLLER_URL', EXAMPLE_URL . '/ChargeCancelController.php');
define('AUTH_REVERSAL_CONTROLLER_URL', EXAMPLE_URL . '/AuthReversalController.php');
define('AUTH_CONTROLLER_URL', EXAMPLE_URL . '/AuthController.php');
define('CHARGE_CONTROLLER_URL', EXAMPLE_URL . '/ChargeController.php');
define('EXAMPLE_PUBLIC_KEY', 's-pub-2a10nxkuA4lC7bIRtz2hKcFGeHhlkr2e'); // todo replace
define('EXAMPLE_PRIVATE_KEY', 's-priv-2a10an6aJK0Jg7sMdpu9gK7ih8pCccze');  // todo replace
