<?php
/**
 * This is the controller for the PayPal example.
 * It is called when the pay button on the index page is clicked.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Unzer;

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

$useExpressCheckout = isset($_POST['express-checkout']) && ($_POST['express-checkout'] === '1');

$paymentTypeId   = $_POST['resourceId'];

$transactionType = $_POST['transaction_type'] ?? 'authorize';

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create an Unzer object using your private key and register a debug handler if you want to.
    $unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
    $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());
    $paymentType = $unzer->fetchPaymentType($paymentTypeId);
    $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

    $basketItem = (new BasketItem())
        ->setAmountPerUnitGross(12.32)
        ->setVat(19.00)
        ->setQuantity(1)
        ->setBasketItemReferenceId('item1')
        ->setTitle('Hat');

    $basket = new Basket($orderId);
    $basket->setTotalValueGross(12.32)
        ->addBasketItem($basketItem)
        ->setCurrencyCode('EUR');

    // Create a charge/authorize transaction to get the redirectUrl.
    if ($transactionType === 'charge') {
        $charge = new Charge(12.32, 'EUR', RETURN_CONTROLLER_URL);
        if ($useExpressCheckout) {
            $charge->setCheckoutType('express', $paymentType);
        }
        $transaction = $unzer->performCharge($charge, $paymentType, null, null, $basket);
    } else {
        $authorize = new Authorization(12.32, 'EUR', RETURN_CONTROLLER_URL);
        if ($useExpressCheckout) {
            $authorize->setCheckoutType('express', $paymentType);
        }
        $transaction = $unzer->performAuthorization($authorize, $paymentType, null, null, $basket);
    }

    // You'll need to remember the paymentId for later in the ReturnController
    $_SESSION['PaymentId'] = $transaction->getPaymentId();
    $_SESSION['ShortId']   = $transaction->getShortId();

    // Redirect to the PayPal page
    if (!$transaction->isError() && $transaction->getRedirectUrl() !== null) {
        redirect($transaction->getRedirectUrl());
    }

    // Check the result message of the charge to find out what went wrong.
    $merchantMessage = $transaction->getMessage()->getCustomer();
} catch (UnzerApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
