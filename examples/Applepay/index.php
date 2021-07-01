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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css" />
    <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
    <style>
        .apple-pay-button {
            display: block;
            background-color: black;
            color: white;
            -webkit-appearance: -apple-pay-button;
            -apple-pay-button-type: plain;
            -apple-pay-button-style: black;
            height: 30px;
            width: 150px;
            -webkit-locale: "us";
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
    <div>
        <div class="button-well">
            <div class="unsupportedBrowserMessage">
                <p>Apple Pay not available
                </p>
            </div>
            <div class="applePayButtonContainer">
                <div class="apple-pay-button apple-pay-button-black" lang="us"
                     onclick="setupApplePaySession()"
                     title="Start Apple Pay" role="link" tabindex="0"></div>
            </div>
        </div>
        <div id="logger">

        </div>
    </div>
</form>

<script>
    checkForApplePayCompatibility();

    function startApplePaySession(applePayInformationObject) {
        if (window.ApplePaySession) {
            let request = createApplePaySessionRequest(applePayInformationObject);
            let session = new ApplePaySession(6, request);
            session.onvalidatemerchant = function (event) {
                applePayInformationObject.onMerchantValidationCallback(session, event);
            }
            session.onpaymentauthorized = function (event) {
                let paymentData = event.payment.token.paymentData;
                logMessage(paymentData);

                let ApplePayObject = {
                    version: paymentData.version,
                    data: paymentData.data,
                    signature: paymentData.signature,
                    header: {
                        ephemeralPublicKey: paymentData.header.ephemeralPublicKey,
                        publicKeyHash: paymentData.header.publicKeyHash,
                        transactionId: paymentData.header.transactionId
                    }
                };

                let jqxhr = $.post('applepaypaymentauthorized.php', {"applePayAuthorisation": JSON.stringify(ApplePayObject)})
                    .done(function (data) {
                        try {
                            let result;
                            logMessage(data);
                            if (JSON.parse(data).result === true) {
                                result = {
                                    "status": ApplePaySession.STATUS_SUCCESS
                                };
                            } else {
                                result = {
                                    "status": ApplePaySession.STATUS_FAILURE
                                };
                            }
                            session.completePayment(result);
                        } catch (e) {
                            alert(e.message);
                        }
                    })
                    .fail(function (error) {
                        logData("onpaymentauthorizedCallbackError", error);
                    });
            }
            session.onpaymentmethodselected = function (event) {
                applePayInformationObject.onPaymentMethodSelectedCallback(event);
                try {
                    session.completePaymentMethodSelection({
                        newTotal: {
                            "label": "Total amount",
                            "amount": "0.00",
                            "type": "final"
                        }
                    });
                } catch (e) {
                    alert(e.message);
                }
            }
            session.onshippingmethodselected = function (event) {
                applePayInformationObject.onShippingMethodSelectedCallback(event);
                try {
                    session.completeShippingMethodSelection(ApplePaySession.STATUS_SUCCESS, 150.00, null);
                } catch (e) {
                    alert(e.message);
                }
            }
            session.oncancel = function (event) {
                applePayInformationObject.onCancelCallback(event);
            }
            session.begin();
        }
    }

    function createApplePaySessionRequest(applePayInformationObject) {
        return {
            countryCode: applePayInformationObject.countryCode,
            currencyCode: applePayInformationObject.currencyCode,
            supportedNetworks: applePayInformationObject.supportedNetworks,
            merchantCapabilities: applePayInformationObject.merchantCapabilities,
            requiredShippingContactFields: applePayInformationObject.requiredBillingContactFields,
            requiredBillingContactFields: applePayInformationObject.requiredBillingContactFields,
            total: {
                label: 'Total amount',
                amount: applePayInformationObject.totalAmount
            }
        };
    }

    function checkForApplePayCompatibility() {
        if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
            $('.unsupportedBrowserMessage').css('display', 'none');
            //$('.applePayButton').css('display', 'block');
            logMessage("Startup Check: Device is capable of making Apple Pay payments");
        }
    }

    function setupApplePaySession() {
        startApplePaySession({
            countryCode: 'DE',
            currencyCode: "EUR",
            totalAmount: 150.00,
            supportedNetworks: ['amex', 'visa', 'masterCard', 'discover'],
            merchantCapabilities: ['supports3DS', 'supportsEMV', 'supportsCredit', 'supportsDebit'],
            requiredShippingContactFields: ['postalAddress', 'name', 'phone', 'email'],
            requiredBillingContactFields: ['postalAddress', 'name', 'phone', 'email'],
            onMerchantValidationCallback: customMerchantValidationCallback,
            onShippingMethodSelectedCallback: customShippingMethodSelectedCallback,
            onPaymentMethodSelectedCallback: customPaymentMethodSelectedCallback,
            onCancelCallback: customCancelCallback
        });
    }

    function customMerchantValidationCallback(session, event) {
        let jqxhr = $.post('merchantvalidation.php', {"merchantValidationUrl": event.validationURL})
            .done(function (data) {
                try {
                    logData(data);
                    let sessionresult = session.completeMerchantValidation(JSON.parse(data));
                } catch (e) {
                    alert('validation' + e.message + "\n" + 'data: ' + data.response);
                }
            })
            .fail(function (error) {
                logData("customMerchantValidationCallbackError", error);
            });
    }

    function customShippingMethodSelectedCallback(event) {
        logData("customShippingMethodSelectedCallback", event);
    }

    function customPaymentMethodSelectedCallback(event) {
        logData("customPaymentMethodSelectedCallback", event);
    }

    function customCancelCallback(event) {
        logData("customCancelCallback", event);
    }

    function logMessage(message) {
        document.getElementById("logger").append(message + '\n');
    }

    function logData(caller, event) {
        let eventMessage = JSON.stringify(getEventProps(event));
        document.getElementById("logger").append(caller + ": " + eventMessage + '\n\n');
    }

    function getEventProps(event) {
        let EventCloneObject = {};
        for (const key in event) {
            const value = event[key];
            if (typeof value === "string") {
                EventCloneObject[key] = value;
            } else if (typeof value === "object") {
                EventCloneObject[key] = value;
            } else if (typeof value === "number") {
                EventCloneObject[key] = value;
            } else if (typeof value === "boolean") {
                EventCloneObject[key] = value;
            }
        }
        return EventCloneObject;
    }
</script>
</body>
</html>
