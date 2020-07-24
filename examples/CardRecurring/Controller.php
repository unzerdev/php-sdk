<?php
/**
 * This is the controller for the Card recurring example.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
/** @noinspection PhpIncludeInspection */
require_once __DIR__ . '/../../../../autoload.php';

use heidelpayPHP\examples\ExampleDebugHandler;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;

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

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    $recurring = $heidelpay->activateRecurringPayment($paymentTypeId, MY_RETURN_CONTROLLER_URL);

    // You'll need to remember the paymentId for later in the ReturnController (in case of 3ds)
    $_SESSION['PaymentTypeId'] = $paymentTypeId;
    $_SESSION['ShortId'] = $recurring->getShortId();

    // Redirect to the 3ds page or to success depending on the state of the transaction
    $redirect = !empty($recurring->getRedirectUrl());
    if (!$redirect && $recurring->isSuccess()) {
        redirect(SUCCESS_URL);
    } elseif ($redirect && $recurring->isPending()) {
        redirect($recurring->getRedirectUrl());
    }

    // Check the result message of the transaction to find out what went wrong.
    $merchantMessage = $recurring->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
