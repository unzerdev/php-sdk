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
use heidelpay\MgwPhpSdk\Heidelpay;

if (!isset($_POST['paymentTypeId'])) {
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

header('Content-Type: application/json');

try {
    $heidelpay     = new Heidelpay(PRIVATE_KEY);
    $authorization = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, AUTH_REVERSAL_CONTROLLER_URL);
    $response[] = [
        'result' => 'success',
        'message' => $authorization->getAmount() . ' ' . $authorization->getCurrency() .
            ' have been authorized for payment ' . $authorization->getPaymentId() . '.'
    ];

    $reversal = $heidelpay->cancelAuthorizationByPayment($authorization->getPaymentId(), 50.00);
    $response[] = [
        'result' => 'success',
        'message' => 'The amount of ' . $reversal->getAmount() . ' ' . $authorization->getCurrency() .
            ' of Authorization ' . $authorization->getId() . ' of payment ' . $authorization->getPaymentId() .
            ' has been canceled .'
    ];

    $charge = $authorization->charge();
    $response[] = [
        'result' => 'success',
        'message' => 'The amount of ' . $charge->getAmount() . ' ' . $charge->getCurrency() .
            ' has been charged for payment ' . $authorization->getPaymentId() . '.'
    ];

    $payment = $charge->getPayment();
    $response[] = [
        'result' => 'info',
        'message' => 'The payment ' . $payment->getId() . ' has the status ' . $payment->getStateName() . '.'
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
