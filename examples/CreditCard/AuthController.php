<?php
/**
 * This is the controller for the authorization transaction for the card example.
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

//#######   Checks whether examples are enabled. #######################################################################
require_once __DIR__ . '/Constants.php';

/**
 * Require the composer autoloader file
 */
require_once __DIR__ . '/../../../../autoload.php';

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Constants\PaymentState;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;

if (!isset($_POST['paymentTypeId'])) {
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

try {
    $heidelpay     = new Heidelpay(PRIVATE_KEY);
    $authorization = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, AUTH_CONTROLLER_URL);

    $response[] = ['result' => 'redirect', 'redirectUrl' => SUCCESS_URL];

} catch (RuntimeException $e) {
    $response[] = ['result' => 'redirect', 'redirectUrl' => FAILURE_URL];
} catch (HeidelpayApiException $e) {
    $response[] = ['result' => 'redirect', 'redirectUrl' => FAILURE_URL];
} catch (HeidelpaySdkException $e) {
    $response[] = ['result' => 'redirect', 'redirectUrl' => FAILURE_URL];
}

header('Content-Type: application/json');
echo json_encode($response);
