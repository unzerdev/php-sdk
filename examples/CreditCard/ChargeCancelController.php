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
use heidelpay\MgwPhpSdk\Resources\Customer;

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

//#######  1. Catch API and SDK errors, write the message to your log and show the ClientMessage to the client. ########
try {
    //#######  2. Create a heidelpay object using your private key. ####################################################
    $heidelpay  = new Heidelpay(PRIVATE_KEY);

    //#######  3. Create a direct charge. ##############################################################################
    $customer      = new Customer('Linda', 'Heideich');
    $charge     = $heidelpay->charge(100.0, Currencies::EURO, $paymentTypeId, CHARGE_CANCEL_CONTROLLER_URL, $customer);
    addSuccess('Charge ' . $charge->getId() . ' has been created for payment ' . $charge->getPaymentId() . '.');

    //#######  4. Create a refund for part of the charged amount. ######################################################
    $cancel     = $charge->cancel(50.0);
    addSuccess('The amount of ' . $cancel->getAmount() . ' ' . $charge->getCurrency() . ' of payment ' .
        $charge->getPaymentId() . ' has been canceled .');

    //#######  5. Fetch the payment object to get the current state. ###################################################
    $payment = $charge->getPayment();
    addInfo('The payment ' . $payment->getId() . ' has the status ' . $payment->getStateName() . '.');

} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException $e) {
    returnError($e->getClientMessage());
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException $e) {
    returnError($e->getClientMessage());
}

returnResponse();

function returnError($message) {
    header('HTTP/1.1 500 Internal Server Error');
    addMessage('error', $message);
    returnResponse();
}

function addSuccess($message) {
    addMessage('success', $message);
}

function addInfo($message) {
    addMessage('info', $message);
}

function addMessage($type, $message) {
    $GLOBALS['response'][] = ['result' => $type, 'message' => $message];
}

function returnResponse() {
    header('Content-Type: application/json');
    echo json_encode($GLOBALS['response']);
    die;
}
