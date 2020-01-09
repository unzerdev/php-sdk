<?php
/**
 * This is the controller for the SEPA direct debit guaranteed example.
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

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use heidelpayPHP\examples\ExampleDebugHandler;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;

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
if (!isset($_POST['paymentTypeId'], $_POST['customerId'])) {
    redirect(FAILURE_URL, 'Resource id is missing!', $clientMessage);
}
$paymentTypeId   = $_POST['paymentTypeId'];
$customerId  = $_POST['customerId'];

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    $orderId = str_replace(['0.', ' '], '', microtime(false));

    // A Basket is mandatory for SEPA direct debit guaranteed payment type
    $basketItem = new BasketItem('Hat', 100.0, 119.0, 1);
    $basket = new Basket($orderId, 119.0, 'EUR', [$basketItem]);

    $transaction = $heidelpay->charge(119.0, 'EUR', $paymentTypeId, CONTROLLER_URL, $customerId, $orderId, null, $basket);

    // You'll need to remember the shortId to show it on the success or failure page
    $_SESSION['ShortId'] = $transaction->getShortId();

    // Redirect to the success or failure depending on the state of the transaction
    $payment = $transaction->getPayment();

    if ($payment->isCompleted()) {
        // The payment process has been successful.
        // You can create the order and show a success page.
        redirect(SUCCESS_URL);
    } elseif ($payment->isPending()) {
        // In case of authorization this is normal since you will later charge the payment.
        // You can create the order with status pending payment and show a success page to the customer if you want.

        // In cases of redirection to an external service (e.g. 3D secure, PayPal, etc) it sometimes takes time for
        // the payment to update it's status. In this case it might be pending at first and change to cancel or success later.
        // Use the webhooks feature to stay informed about changes of the payment (e.g. cancel, success)
        // then you can cancel the order later or mark it paid as soon as the event is triggered.

        // In any case, the payment is not done when the payment is pending and you should ship until it changes to success.
        redirect(PENDING_URL);
    }
    // If the payment is neither success nor pending something went wrong.
    // In this case do not create the order.
    // Redirect to an error page in your shop and show an message if you want.

    // Check the result message of the transaction to find out what went wrong.
    $merchantMessage = $transaction->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
