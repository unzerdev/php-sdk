<?php
/**
 * This file provides a list of the example implementations.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\examples
 */

use heidelpayPHP\Validators\PrivateKeyValidator;
use heidelpayPHP\Validators\PublicKeyValidator;

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../autoload.php';

function printMessage($type, $title, $text)
{
    echo '<div class="ui ' . $type . ' message">'.
        '<div class="header">' . $title . '</div>'.
        '<p>' . nl2br($text) . '</p>'.
        '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>
            Heidelpay UI Examples
        </title>
        <script src="https://code.jquery.com/jquery-3.1.1.min.js"
                integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />

        <link rel="stylesheet" href="https://static.heidelpay.com/v1/heidelpay.css" />
        <script type="text/javascript" src="https://static.heidelpay.com/v1/heidelpay.js"></script>
    </head>

    <body style="margin: 70px 70px 0;">
        <div class="ui container segment">
            <h2 class="ui header">
                <i class="shopping cart icon"></i>
                <span class="content">
                    Payment Implementation Examples
                    <span class="sub header">Choose the Payment Type you want to evaluate ...</span>
                </span>
            </h2>

            <?php
                // Show info message if the key pair is invalid
                if (
                    !PrivateKeyValidator::validate(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY) ||
                    !PublicKeyValidator::validate(HEIDELPAY_PHP_PAYMENT_API_PUBLIC_KEY)
                ) {
                    printMessage(
                        'yellow',
                        'Attention: You need to provide a valid key pair!',
                        "The key pair provided in file _enableExamples.php does not seem to be valid.\n".
                        'Please contact our support to get a test key pair <a href="mailto:support@heidelpay.com">support@heidelpay.com</a>'
                    );
                }
            ?>

            <div class="ui four cards">
                <div class="card olive">
                    <div class="content">
                        <div class="header">Card</div>
                        <div class="description">
                            You can try authorize, charge and payout transactions with or without 3Ds.
                        </div>
                    </div>
                    <div id="tryCardExample" class="ui bottom attached green button" onclick="location.href='Card/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">Card Recurring</div>
                        <div class="description">
                            You can set a Card type to recurring in order to register it and charge later as well as implement recurring payments.
                        </div>
                    </div>
                    <div id="tryCardRecurringExample" class="ui bottom attached green button" onclick="location.href='CardRecurring/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            EPS
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryEPSExample" class="ui bottom attached green button" onclick="location.href='EPSCharge/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            iDeal
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryIDealExample" class="ui bottom attached green button" onclick="location.href='IDeal/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Giropay
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryGiropayExample" class="ui bottom attached green button" onclick="location.href='Giropay/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Alipay
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryAlipayExample" class="ui bottom attached green button" onclick="location.href='Alipay/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            WeChat Pay
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryWechatPayExample" class="ui bottom attached green button" onclick="location.href='Wechatpay/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Przelewy24 (P24)
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryP24Example" class="ui bottom attached green button" onclick="location.href='Przelewy24/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Prepayment
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryPrePaymentExample" class="ui bottom attached green button" onclick="location.href='Prepayment/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Invoice
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryInvoiceExample" class="ui bottom attached green button" onclick="location.href='Invoice/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Invoice guaranteed
                        </div>
                        <div class="description">
                            This example adds the necessary customer data within the checkout form.
                            Please refer to the example of <i>Invoice Factoring</i> if you don't want to add the customer via payment form.
                        </div>
                    </div>
                    <div id="tryInvoiceGuaranteedExample" class="ui bottom attached green button" onclick="location.href='InvoiceGuaranteed/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Invoice Factoring
                        </div>
                        <div class="description">
                            This example adds the necessary customer data within the php controller.
                            Please refer to the example of <i>Invoice guaranteed</i> if you want to add the customer data withing the payment form.
                        </div>
                    </div>
                    <div id="tryInvoiceFactoringExample" class="ui bottom attached green button" onclick="location.href='InvoiceFactoring/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            PayPal
                        </div>
                        <div class="description">
                            You can try authorize and direct charge.
                        </div>
                    </div>
                    <div id="tryPayPalExample" class="ui bottom attached green button" onclick="location.href='PayPal/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            PayPal Recurring
                        </div>
                        <div class="description">
                            You can set a Pay Pal type to recurring in order to register it and charge later as well as implement recurring payments.
                        </div>
                    </div>
                    <div id="tryPayPalRecurringExample" class="ui bottom attached green button" onclick="location.href='PayPalRecurring/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Sofort
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="trySofortExample" class="ui bottom attached green button" onclick="location.href='Sofort/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            SEPA direct debit guaranteed
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryDirectDebitGuaranteedExample" class="ui bottom attached green button" onclick="location.href='SepaDirectDebitGuaranteed/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            FlexiPay Rate Direct Debit (Hire Purchase)
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryHirePurchaseDirectDebitExample" class="ui bottom attached green button" onclick="location.href='HirePurchaseDirectDebit/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            FlexiPay Direct (PIS)
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryFlexiPayDirectExample" class="ui bottom attached green button" onclick="location.href='FlexiPayDirect/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Hosted Payment Page
                        </div>
                        <div class="description">
                            This example shows how to use the Payment Page hosted externally.
                            The customer will be redirected to a Payment Page on a heidelpay
                            server and redirected to a given RedirectUrl.
                        </div>
                    </div>
                    <div class="ui attached white button" onclick="location.href='https://docs.heidelpay.com/docs/payment-page/';">
                        Documentation
                    </div>
                    <div id="tryHostedPayPageExample" class="ui bottom attached green button" onclick="location.href='HostedPayPage/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Embedded Payment Page
                        </div>
                        <div class="description">
                            This example shows how to use the embedded Payment Page.
                            The Payment Page will be shown as an Overlay in your own shop.
                        </div>
                    </div>
                    <div class="ui attached white button" onclick="location.href='https://docs.heidelpay.com/docs/payment-page/';">
                        Documentation
                    </div>
                    <div id="tryEmbeddedPayPageExample" class="ui bottom attached green button" onclick="location.href='EmbeddedPayPage/';">
                        Try
                    </div>
                </div>
            </div>
        </div>

        <div class="ui container segment">
            <h2 class="ui header">
                <i class="bolt icon"></i>
                <span class="content">
                    Webhook Implementation Examples
                    <span class="sub header">Enable or disable webhooks ...</span>
                </span>
            </h2>
            <div class="ui three cards">
                <div class="card">
                    <div class="content">
                        <div class="header">
                            Register Webhooks
                        </div>
                        <div class="description">
                            Enable a log output in ExampleDebugHandler to see the events coming in.
                        </div>
                    </div>
                    <div class="ui bottom attached blue button" onclick="location.href='Webhooks/';">
                        Try
                    </div>
                </div>
                <div class="card">
                    <div class="content">
                        <div class="header">
                            Delete all Webhooks
                        </div>
                        <div class="description">
                            Delete all Webhooks corresponding to this key pair.
                        </div>
                    </div>
                    <div class="ui bottom attached blue button" onclick="location.href='Webhooks/removeAll.php';">
                        Try
                    </div>
                </div>
                <div class="card">
                    <div class="content">
                        <div class="header">
                            Fetch all Webhooks
                        </div>
                        <div class="description">
                            Fetch all Webhooks corresponding to this key pair.
                        </div>
                    </div>
                    <div class="ui bottom attached blue button" onclick="location.href='Webhooks/fetchAll.php';">
                        Try
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>
