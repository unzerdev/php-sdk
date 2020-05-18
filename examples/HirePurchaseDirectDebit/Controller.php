<?php
/**
 * This is the controller for the hire purchase direct debit example.
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
use heidelpayPHP\Resources\CustomerFactory;
use heidelpayPHP\Resources\EmbeddedResources\Address;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;

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
if (!isset($_POST['paymentTypeId'])) {
    redirect(FAILURE_URL, 'Payment type id is missing!', $clientMessage);
}
$paymentTypeId   = $_POST['paymentTypeId'];

// Catch API errors, write the message to your log and show the ClientMessage to the client.
/** @noinspection BadExceptionsProcessingInspection */
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Use the quote or order id from your shop
    $orderId = 'o' . str_replace(['0.', ' '], '', microtime(false));

    /** @var HirePurchaseDirectDebit $paymentType */
    $paymentType = $heidelpay->fetchPaymentType($paymentTypeId);

    // A customer with matching addresses is mandatory for Invoice Factoring payment type
    $address  = (new Address())
        ->setName('Linda Heideich')
        ->setStreet('Vangerowstr. 18')
        ->setCity('Heidelberg')
        ->setZip('69155')
        ->setCountry('DE');
    $customer = CustomerFactory::createCustomer('Linda', 'Heideich')
        ->setBirthDate('2000-02-12')
        ->setBillingAddress($address)
        ->setShippingAddress($address)
        ->setEmail('linda.heideich@test.de');

    // A Basket is mandatory for SEPA direct debit guaranteed payment type
    $basketItem = (new BasketItem('Hat', 100.00, 100.00, 1))
        ->setAmountNet(100.0)
        ->setAmountGross(119.0)
        ->setAmountVat(19.0);
    $basket = (new Basket($orderId, 119.0, 'EUR', [$basketItem]))->setAmountTotalVat(19.0);

    // initialize the payment
    $authorize = $heidelpay->authorize(
        $paymentType->getTotalPurchaseAmount(),
        'EUR',
        $paymentType,
        CONTROLLER_URL,
        $customer,
        $orderId,
        null,
        $basket
    );

    // You'll need to remember the shortId to show it on the success or failure page
    $_SESSION['PaymentId'] = $authorize->getPaymentId();

    // Redirect to the success or failure depending on the state of the transaction
    if ($authorize->isSuccess()) {
        redirect(CONFIRM_URL);
    }

    // Check the result message of the transaction to find out what went wrong.
    $merchantMessage = $authorize->getMessage()->getCustomer();
} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
