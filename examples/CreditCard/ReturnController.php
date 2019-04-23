<?php
/**
 * This is the return controller for the Card example.
 * It is called when the client is redirected back to the shop from the 3ds page.
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
use heidelpayPHP\Resources\TransactionTypes\Authorization;

session_start();

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage']   = $clientMessage;
    header('Location: ' . $url);
    die();
}

// Retrieve the paymentId you remembered within the Controller
if (!isset($_SESSION['PaymentId'])) {
    redirect(FAILURE_URL, 'The payment id is missing.', $clientMessage);
}
$paymentId = $_SESSION['PaymentId'];

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Redirect to success if the payment has been successfully completed or is still in handled.
    $payment = $heidelpay->fetchPayment($paymentId);
    if (
        $payment->isCompleted()         // <<---- in case of charge
        || $payment->isPending()        // <<---- in case of authorize
    ) {
        redirect(SUCCESS_URL);
    }

    // Check the result message of the transaction to find out what went wrong.
    $transaction = $payment->getAuthorization();
    if ($transaction instanceof Authorization) {
        $merchantMessage = $transaction->getMessage()->getCustomer();
    } else {
        $transaction = $payment->getChargeByIndex(0);
        $merchantMessage = $transaction->getMessage()->getCustomer();
    }
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
