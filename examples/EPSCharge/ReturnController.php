<?php
/**
 * This is the controller for the 'Authorization' transaction example for Card.
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

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use heidelpayPHP\examples\ExampleDebugHandler;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;

$clientMessage = 'Something went wrong. Please try again later.';

function redirect($url)
{
    header('Location: ' . $url);
    die();
}

session_start();

if (!isset($_SESSION['PaymentId'])) {
    redirect(FAILURE_URL);
}

// Retrieve the paymentId you remembered within the Controller
$paymentId = $_SESSION['PaymentId'];

//  Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay('s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Redirect to success if the payment has been successfully completed.
    $payment   = $heidelpay->fetchPayment($paymentId);
    if ($payment->isCompleted()) {
        redirect(SUCCESS_URL);
    }

    // Check the result message of the charge to find out what went wrong.
    $charge = $payment->getChargeByIndex(0);
    $merchantMessage = $charge->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (\RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
$_SESSION['merchantMessage'] = $merchantMessage;
$_SESSION['clientMessage']   = $clientMessage;
redirect(FAILURE_URL);
