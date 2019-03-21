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
use heidelpayPHP\Resources\Customer;

session_start();
session_unset();

function redirect($url)
{
    header('Location: ' . $url);
    die();
}

if (!isset($_POST['resourceId'])) {
    redirect(FAILURE_URL);
}

$paymentTypeId   = $_POST['resourceId'];
$transactionType = $_POST['transaction_type'] ?? 'authorize';
$use3Ds          = isset($_POST['3dsecure']) && ($_POST['3dsecure'] === '1');

//#######  1. Catch API and SDK errors, write the message to your log and show the ClientMessage to the client. ########
try {
    //#######  2. Create a heidelpay object using your private key #####################################################
    $heidelpay = new Heidelpay('s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    //#######  3. Create an authorization (aka reservation) ############################################################
    $customer            = new Customer('Linda', 'Heideich');

    $transaction = $transactionType === 'charge' ?
        $heidelpay->charge(12.99, 'EUR', $paymentTypeId, RETURN_CONTROLLER_URL, $customer, null, null, null, $use3Ds) :
        $heidelpay->authorize(12.99, 'EUR', $paymentTypeId, RETURN_CONTROLLER_URL, $customer, null, null, null, $use3Ds);

    $_SESSION['PaymentId'] = $transaction->getPaymentId();
    $_SESSION['ShortId'] = $transaction->getShortId();

    $payment = $transaction->getPayment();
    if ($transaction->getRedirectUrl() === null && $transaction->isSuccess()) {
        redirect(SUCCESS_URL);
    } elseif ($transaction->getRedirectUrl() !== null && $transaction->isPending()) {
        redirect($transaction->getRedirectUrl());
    }
    $_SESSION['merchantMessage'] = 'Something went wrong!';
} catch (HeidelpayApiException $e) {
    $_SESSION['merchantMessage'] = $e->getMerchantMessage();
    $_SESSION['clientMessage'] = $e->getClientMessage();
} catch (\RuntimeException $e) {
    $_SESSION['merchantMessage'] = $e->getMessage();
}
$_SESSION['clientMessage'] = 'Something went wrong. Please try again.';
redirect(FAILURE_URL);
