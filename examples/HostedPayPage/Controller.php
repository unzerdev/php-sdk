<?php
/**
 * This is the controller for the Hosted Payment Page example.
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
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\Resources\PaymentTypes\Paypage;

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

// These lines are just for this example
$transactionType = $_POST['transaction_type'] ?? 'authorize';

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create a heidelpay object using your private key and register a debug handler if you want to.
    $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // Create a charge/authorize transaction
    $customer = CustomerFactory::createCustomer('Max', 'Mustermann');

    // These are the mandatory parameters for the payment page ...
    $paypage = new Paypage(119.0, 'EUR', RETURN_CONTROLLER_URL);

    $orderId = str_replace(['0.', ' '], '', microtime(false));

    // ... however you can customize the Payment Page using additional parameters.
    $paypage->setLogoImage('https://dev.heidelpay.com/devHeidelpay_400_180.jpg')
            ->setFullPageImage('https://www.heidelpay.com/fileadmin/content/header-Imges-neu/Header_Phone_12.jpg')
            ->setShopName('My Test Shop')
            ->setShopDescription('Best shop in the whole world!')
            ->setTagline('Try and stop us from being awesome!')
            ->setOrderId('OrderNr' . $orderId)
            ->setTermsAndConditionUrl('https://www.heidelpay.com/en/')
            ->setPrivacyPolicyUrl('https://www.heidelpay.com/de/')
            ->setImprintUrl('https://www.heidelpay.com/it/')
            ->setHelpUrl('https://www.heidelpay.com/at/')
            ->setContactUrl('https://www.heidelpay.com/en/about-us/about-heidelpay/')
            ->setInvoiceId('InvoiceNr' . microtime(true));

    // ... in order to enable FlexiPay Rate (Hire Purchase) you will need to set the effectiveInterestRate as well.
    $paypage->setEffectiveInterestRate(4.99);

    // ... a Basket is mandatory for HirePurchase
    $basketItem = new BasketItem('Hat', 100.0, 119.0, 1);
    $basket = new Basket($orderId, 119.0, 'EUR', [$basketItem]);

    if ($transactionType === 'charge') {
        $heidelpay->initPayPageCharge($paypage, $customer, $basket);
    } else {
        $heidelpay->initPayPageAuthorize($paypage, $customer, $basket);
    }

    $_SESSION['PaymentId'] = $paypage->getPaymentId();

    // Redirect to the paypage
    redirect($paypage->getRedirectUrl());

} catch (HeidelpayApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
redirect(FAILURE_URL, $merchantMessage, $clientMessage);
