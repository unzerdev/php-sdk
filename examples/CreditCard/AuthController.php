<?php
/**
 * This is the controller for the 'Authorization' transaction example for Card.
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
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;

if (!isset($_POST['paymentTypeId'])) {
    redirect(FAILURE_URL);
}
$paymentTypeId   = $_POST['paymentTypeId'];

session_start();

//#######  1. Catch API and SDK errors, write the message to your log and show the ClientMessage to the client. ########
try {
    //#######  2. Create a heidelpay object using your private key #####################################################
    $heidelpay     = new Heidelpay(PRIVATE_KEY);

    //#######  3. Create an authorization (aka reservation) ############################################################
    $customer      = new Customer('Linda', 'Heideich');
    $authorization = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, AUTH_CONTROLLER_URL, $customer);

} catch (HeidelpayApiException $e) {
    //#######  5. In case of an error redirect to your failure page. ###################################################
} catch (HeidelpaySdkException $e) {
}

//#######  6. If everything is fine redirect to your success page. #####################################################
if ($authorization->getPayment() instanceof Payment) {
    $_SESSION['paymentId'] = $authorization->getPaymentId();
    redirect(SUCCESS_URL);
}
redirect(FAILURE_URL);

function redirect($url) {
    $response[] = ['result' => 'redirect', 'redirectUrl' => $url];
    header('Content-Type: application/json');
    echo json_encode($response);
    die;
}
