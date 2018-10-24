<?php
/**
 * This is the controller for the charge transaction for the card example.
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
use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Heidelpay;

//#######   Checks whether examples are enabled. #######################################################################
require_once __DIR__ . '/Constants.php';

/**
 * Require the composer autoloader file
 */
require_once __DIR__ . '/../../../../autoload.php';

if (!isset($_POST['paymentTypeId'])) {
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

header('Content-Type: application/json');

try {
    $heidelpay  = new Heidelpay(PRIVATE_KEY);
    $charge     = $heidelpay->charge(100.0, Currencies::EURO, $paymentTypeId, CHARGE_CONTROLLER_URL);

    $response[] = [
        'result' => 'success',
        'message' => 'Charge ' . $charge->getId() . ' has been created for payment ' . $charge->getPaymentId() . '.'
    ];

} catch (RuntimeException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getMessage()];
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getClientMessage()];
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getClientMessage()];
}

echo json_encode($response);
