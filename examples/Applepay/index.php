<?php
/**
 * This file provides an example implementation of the Apple Pay payment type.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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
 * @author  David Owusu <development@unzer.com>
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

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css" />
    <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
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
            </div id="logger">
            <div
        </div>
    </div>
</form>
<div id="logger">

</div>

<script>
    const $errorHolder = $('#error-holder');

    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');
    const unzerApplePayInstance = unzerInstance.ApplePay();

    function startApplePaySession(applePayInformationObject) {
        if (window.ApplePaySession) {
            let request = createApplePaySessionRequest(applePayInformationObject);
            let session = new ApplePaySession(6, request);
            session.onvalidatemerchant = function (event) {
                applePayInformationObject.onMerchantValidationCallback(session, event);
            }
            session.onpaymentauthorized = function (event) {
                let paymentData = event.payment.token.paymentData;
                const $form = $('form[id="payment-form"]');
                let formObject = QueryStringToObject($form.serialize());

                // Create an Unzer instance with your public key

                unzerApplePayInstance.createResource(paymentData)
                    .then(function (createdResource) {
                        formObject.typeId = createdResource.id;
                        // Hand over the type ID to your backend.
                        $.post('./Controller.php', JSON.stringify(formObject), null, 'json')
                            .done(function (result) {
                                // Handle the transaction respone from backend.
                                let status = result.transactionStatus;
                                if (status === 'success' || status === 'pending') {
                                    session.completePayment({status: window.ApplePaySession.STATUS_SUCCESS});
                                    window.location.href = '<?php echo RETURN_CONTROLLER_URL; ?>';
                                } else {
                                    window.location.href = '<?php echo FAILURE_URL; ?>';
                                    abortPaymentSession(session);
                                    session.abort();
                                }
                            })
                            .fail(function (error) {
                                handleError(error.statusText);
                                abortPaymentSession(session);
                            });
                    })
                    .catch(function (error) {
                        handleError(error.message);
                        abortPaymentSession(session);
                    })
            }
            session.onpaymentmethodselected = function (event) {
                applePayInformationObject.onPaymentMethodSelectedCallback(event);
                try {
                    session.completePaymentMethodSelection({
                        newTotal: {
                            'label': 'Total amount',
                            'amount': '12.99',
                            'type': 'final'
                        },
                        newLineItems: [
                            {
                                "label": "Bag Subtotal",
                                "type": "final",
                                "amount": "10.00"
                            },
                            {
                                "label": "Free Shipping",
                                "amount": "0.00",
                                "type": "final"
                            },
                            {
                                "label": "Estimated Tax",
                                "amount": "2.99",
                                "type": "final"
                            }
                        ]
                    });
                } catch (e) {
                    alert(e.message);
                }
            }
            session.onshippingmethodselected = function (event) {
                let status = ApplePaySession.STATUS_SUCCESS;
                let newTotal = {
                    'label': 'Total amount',
                    'amount': '12.99',
                    'type': 'final'
                }
                session.completeShippingMethodSelection(status, newTotal);
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
            },
            lineItems: [
                {
                    "label": "Bag Subtotal",
                    "type": "final",
                    "amount": "35.00"
                },
                {
                    "label": "Free Shipping",
                    "amount": "0.00",
                    "type": "final"
                },
                {
                    "label": "Estimated Tax",
                    "amount": "3.06",
                    "type": "final"
                }
            ]
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
        let jqxhr = $.post('./merchantvalidation.php', JSON.stringify({"merchantValidationUrl": event.validationURL}), null, 'json')
            .done(function (validationResponse) {
                try {
                    session.completeMerchantValidation(validationResponse);
                } catch (e) {
                    alert(e.message);
                }

            })
            .fail(function (error) {
                logData("customMerchantValidationCallbackError", error);
            });
    }

    function customShippingMethodSelectedCallback(event) {
    }

    function customPaymentMethodSelectedCallback(event) {
    }

    function customCancelCallback(event) {
    }

    function logMessage(message) {
        document.getElementById("logger").append(message + '\n');
    }

    function logData(caller, event) {
        let eventMessage = JSON.stringify(getEventProps(event));
        document.getElementById("logger").append(caller + ": " + eventMessage + '\n\n');
    }

    // Translates query string to object
    function QueryStringToObject(queryString) {
        let pairs = queryString.slice().split('&');

        let result = {};
        pairs.forEach(function(pair) {
            pair = pair.split('=');
            result[pair[0]] = decodeURIComponent(pair[1] || '');
        });
        return JSON.parse(JSON.stringify(result));
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

