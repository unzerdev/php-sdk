<?php
/**
 * This file provides an example implementation of the Card payment type.
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

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unzer UI Examples</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://dev-static.unzer.com/v1/unzer.css" />
    <script type="text/javascript" src="https://dev-static.unzer.com/v1/unzer.js"></script>
    <style>
        .apple-pay-button {
            display: inline-block;
            -webkit-appearance: -apple-pay-button;
            -apple-pay-button-type: buy;
            -apple-pay-button-style: black;
        }

        .button-well {
            text-align: center;
            position: relative;
            background: #f1f1f1;
            border-radius: 3px;
            -webkit-transition: background 0.3s;
            transition: background 0.3s;
            margin: 10px;
            border: 1px solid transparent;
        }

        .unsupportedBrowserMessage {
            color: #888;
            padding: .375rem .75rem;
            cursor: not-allowed;
            line-height: 1.5;
        }

        .unsupportedBrowserMessage p {
            margin: 0;
        }

        .applePayButtonContainer {
            position: relative;
        }
    </style>
</head>

<body style="margin: 70px 70px 0;">

<p><a href="https://docs.unzer.com/docs/testdata" target="_blank">Click here to open our test data in new tab.</a></p>

<form id="payment-form" class="unzerUI form" novalidate>
    <!-- This is just for the example - Start -->
    <div class="fields inline">
        <label for="transaction_type">Chose the transaction type you want to test:</label>
        <div class="field">
            <div class="unzerUI radio checkbox">
                <input type="radio" name="transaction_type" value="authorize" checked="">
                <label>Authorize</label>
            </div>
        </div>
        <div class="field">
            <div class="unzerUI radio checkbox">
                <input type="radio" name="transaction_type" value="charge">
                <label>Charge</label>
            </div>
        </div>
    </div>
    <!-- This is just for the example - End -->

    <div>
        <div class="field" id="error-holder" style="color: #9f3a38"> </div>
        <div class="button-well">
            <div class="applePayButtonContainer">
                <div class="apple-pay-button apple-pay-button-black" lang="us"
                     onclick="setupApplePaySession()"
                     title="Start Apple Pay" role="link" tabindex="0"></div>
            </div>
        </div>
    </div>
</form>

<script>
    const $errorHolder = $('#error-holder');

    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');

    const APPLE_PAY_VERSION = 6;

    // Why don't we do that for the merchant, when creating the ApplePay instance?
    if (!window.ApplePaySession || !ApplePaySession.canMakePayments() || !ApplePaySession.supportsVersion(APPLE_PAY_VERSION)) {
        handleError('This device does not support Apple Pay version 6!', APPLE_PAY_VERSION);
    } else {
        var unzerApplePayInstance = unzerInstance.ApplePay();

        var applePayInformationObject = unzerApplePayInstance.createApplePaySessionRequest({
            countryCode: 'DE',
            currencyCode: 'EUR',
            supportedNetworks: ['visa', 'masterCard'],
            merchantCapabilities: ['supports3DS', 'supportsCredit', 'supportsDebit'],
            totalLabel: '"Unzer UI Demo (Card is not charged)"',
            totalAmount: '0.0',

            onMerchantValidationCallback: (session, event) => {
                makeRequest('POST', './merchantvalidation.php', JSON.stringify({"merchantValidationUrl": event.validationURL}))
                    .then(function (e) {
                        const merchantSession = JSON.parse(e.target.response);
                        session.completeMerchantValidation(merchantSession)
                    }, function (e) {
                        // handle errors
                        handle('There has been an error validating the merchant. Please try again later.' + e.message)
                    });
            },

            onPaymentAuthorizedCallBack: (session, event) => {
                var paymentData = event.payment.token.paymentData;
                const $form = $('form[id="payment-form"]');
                let formObject = QueryStringToObject($form.serialize());

                unzerApplePayInstance.createResource(paymentData)
                    .then(function (createdResource) {
                        formObject.typeId = createdResource.id;
                        makeRequest('POST', './Controller.php', JSON.stringify(formObject))
                            .then(function (e) {
                                let paymentAuthorizedResponse = JSON.parse(e.target.response);
                                let paymentAuthorizedResult;

                                if (paymentAuthorizedResponse.result === true) {
                                    paymentAuthorizedResult =  { status: window.ApplePaySession.STATUS_SUCCESS };
                                } else {
                                    paymentAuthorizedResult =  { status: window.ApplePaySession.STATUS_FAILURE };
                                    // todo error holder update
                                }

                                session.completePayment(JSON.stringify(paymentAuthorizedResult));
                            }, function (e) {
                                session.completePayment(JSON.stringify({ status: window.ApplePaySession.STATUS_FAILURE }));
                                // todo error holder update
                            });
                    })
                    .catch(function (error) {
                        // todo error holder update
                        session.abort();
                    })
            },

            onShippingMethodSelectedCallback: (session, event) => {
                var status = ApplePaySession.STATUS_SUCCESS;
                var newTotal = {
                    'label': 'Total amount',
                    'amount': '12.99',
                    'type': 'final'
                }
                var newLineItems =[]; // What does this mean?
                session.completeShippingMethodSelection(status, newTotal, newLineItems);
            },

            onPaymentMethodSelectedCallback: (session, event) => {
                session.completePaymentMethodSelection({
                    newTotal: {
                        'label': 'Total amount',
                        'amount': '12.99',
                        'type': 'final'
                    }
                });
            },

            onCancelCallback: (event) => {
                handleError('Canceled by user')
            }
        })

        // Initiates the Apple Pay session using the data defined in the applePayInformationObject.
        function setupApplePaySession() {
            unzerApplePayInstance.startApplePaySession(applePayInformationObject)
            handleError('');
        }

        // Helps performing ajax calls, e.g. to the server-to-server integration.
        function makeRequest (method, url, body) {
            return new Promise(function (resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.withCredentials = false
                xhr.open(method, url);
                xhr.onload = resolve;
                xhr.onerror = reject;
                if (body) {
                    xhr.send(body);
                } else {
                    xhr.send();
                }
            });
        }

        // Translates query string to object
        function QueryStringToObject(queryString) {
            var pairs = queryString.slice().split('&');

            var result = {};
            pairs.forEach(function(pair) {
                pair = pair.split('=');
                result[pair[0]] = decodeURIComponent(pair[1] || '');
            });
            return JSON.parse(JSON.stringify(result));
        }
    }

    // Updates the error holder with the given message.
    function handleError (message) {
        $errorHolder.html(message);
    }

</script>
</body>
</html>
