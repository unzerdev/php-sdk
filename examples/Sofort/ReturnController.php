<?php
/**
 * This is the return controller for the Sofort example.
 * It is called when the client is redirected back to the shop from the external page.
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
use heidelpayPHP\Resources\TransactionTypes\Charge;

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage']   = $clientMessage;
    header('Location: ' . $url);
    die();
}

session_start();

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

    // Redirect to success if the payment has been successfully completed.
    $payment   = $heidelpay->fetchPayment($paymentId);

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
    $transaction = $payment->getChargeByIndex(0);
    if (!$transaction instanceof Charge) {
        $transaction = $payment->getChargeByIndex(0);
    }
    $merchantMessage = $transaction->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);

