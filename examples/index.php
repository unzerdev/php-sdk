<?php
/**
 * This file provides a list of the example implementations.
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
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\examples
 */

use UnzerSDK\Validators\PrivateKeyValidator;
use UnzerSDK\Validators\PublicKeyValidator;

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
        <title>Unzer UI Examples</title>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
                integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />

        <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css" />
        <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
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
                    !PrivateKeyValidator::validate(UNZER_PAPI_PRIVATE_KEY) ||
                    !PublicKeyValidator::validate(UNZER_PAPI_PUBLIC_KEY)
                ) {
                    printMessage(
                        'yellow',
                        'Attention: You need to provide a valid key pair!',
                        "The key pair provided in file _enableExamples.php does not seem to be valid.\n".
                        'Please contact our support to get a test key pair <a href="mailto:support@unzer.com">support@unzer.com</a>'
                    );
                }
            ?>

            <div class="ui four cards">
                <div class="card olive">
                    <div class="content">
                        <div class="header">Card</div>
                        <div class="description">
                            You can try authorize, charge and payout transactions with or without 3Ds.
                            This example submits email <b>via customer</b> resource.
                        </div>
                    </div>
                    <div id="tryCardExample" class="ui bottom attached green button" onclick="location.href='Card/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">Card (extended)</div>
                        <div class="description">
                            Including email and holder fields.
                            Adding more information to the card can improve risk acceptance.
                            This example submits email <b>via payment type</b>  resource.
                        </div>
                    </div>
                    <div id="tryCardExample" class="ui bottom attached green button" onclick="location.href='CardExtended/';">
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
                            Unzer Invoice
                        </div>
                    </div>
                    <div id="tryInvoiceSecuredExample" class="ui bottom attached green button" onclick="location.href='InvoiceSecured/';">
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
                            You can set a PayPal type to recurring in order to register it and charge later as well as implement recurring payments.
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
                            Unzer Direct Debit
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryDirectDebitSecuredExample" class="ui bottom attached green button" onclick="location.href='SepaDirectDebitSecured/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Unzer Installment (secured)
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryInstallmentSecuredExample" class="ui bottom attached green button" onclick="location.href='InstallmentSecured/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Unzer Bank Transfer (PIS)
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryUnzerBankTransferExample" class="ui bottom attached green button" onclick="location.href='BankTransfer/';">
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
                            The customer will be redirected to a Payment Page on a Unzer
                            server and redirected to a given RedirectUrl.
                        </div>
                    </div>
                    <div class="ui attached white button" onclick="location.href='https://docs.unzer.com/accept-payments/accept-payments-payment-page';">
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
                    <div class="ui attached white button" onclick="location.href='https://docs.unzer.com/accept-payments/accept-payments-payment-page';">
                        Documentation
                    </div>
                    <div id="tryEmbeddedPayPageExample" class="ui bottom attached green button" onclick="location.href='EmbeddedPayPage/';">
                        Try
                    </div>
                </div>
                <div class="card olive">
                    <div class="content">
                        <div class="header">
                            Bancontact
                        </div>
                        <div class="description">
                        </div>
                    </div>
                    <div id="tryBancontactExample" class="ui bottom attached green button" onclick="location.href='Bancontact/';">
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
