<?php
/**
 * This is the controller for the Card example.
 * It is called when the pay button on the index page is clicked.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
use heidelpayPHP\Resources\Customer;

session_start();
session_unset();

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage']   = $clientMessage;
    header('Location: ' . $url);
    die();
}

// You will need the id of the payment type created in the frontend (index.php)
if (!isset($_POST['resourceId'])) {
    redirect(FAILURE_URL, 'Resource id is missing!', $clientMessage);
}
$paymentTypeId   = $_POST['resourceId'];

// These lines are just for this example
$transactionType = $_POST['transaction_type'] ?? 'authorize';
$use3Ds          = isset($_POST['3dsecure']) && ($_POST['3dsecure'] === '1');

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Create a charge/authorize transaction
    // The 3D secured flag can be used to switch between 3ds and non-3ds.
    // If your merchant is only configured for one of those you can omit the flag.
    $customer     = new Customer('Linda', 'Heideich');
    if ($transactionType === 'charge') {
        $transaction = $heidelpay->charge(12.99, 'EUR', $paymentTypeId, RETURN_CONTROLLER_URL, $customer, null, null, null, $use3Ds);
    } else {
        $transaction = $heidelpay->authorize(12.99, 'EUR', $paymentTypeId, RETURN_CONTROLLER_URL, $customer, null, null, null, $use3Ds);
    }

    // You'll need to remember the paymentId for later in the ReturnController (in case of 3ds)
    $_SESSION['PaymentId'] = $transaction->getPaymentId();
    $_SESSION['ShortId'] = $transaction->getShortId();

    // Redirect to the 3ds page or to success depending on the state of the transaction
    $payment = $transaction->getPayment();
    if ($transaction->getRedirectUrl() === null && $transaction->isSuccess()) {
        redirect(SUCCESS_URL);
    } elseif ($transaction->getRedirectUrl() !== null && $transaction->isPending()) {
        redirect($transaction->getRedirectUrl());
    }

    // Check the result message of the transaction to find out what went wrong.
    $merchantMessage = $transaction->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
