<?php
/**
 * This is the controller handling the redirect from Paypal back into the shop.
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

use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

session_start();

$paymentId = $_SESSION['paymentId'];

try {
    $heidelpay = new Heidelpay(EXAMPLE_PRIVATE_KEY);
    $payment = $heidelpay->fetchPayment($paymentId);

    if ($payment->isCompleted()) {
        header('Location: ' . SUCCESS_URL);
        exit();
    }
} catch (HeidelpaySdkException $e) {
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException $e) {
}
header('Location: ' . FAILURE_URL);
exit();
