<?php
/**
 * This file provides an example implementation of the Paylater Installment payment type.
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Unzer UI Examples</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css"/>
    <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
</head>

<body style="margin: 70px 70px 0;">

<p><a href="https://docs.unzer.com/reference/test-data" target="_blank">Click here to open our test data in new tab.</a><br/>
</p>

<form id="payment-form-paylater-installment" class="unzerUI form unzerUI-paylater-installment__form" novalidate>
    <div id="pit-dimmer-holder" class="ui active dimmer" style="display: block">
        <div class="ui loader"></div>
    </div>

    <div id="example-paylater-installment"></div>
    <div id="error-holder" class="field" style="color: #d0021b"></div>

    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none">
        Continue
    </button>
</form>

<script>
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');
    let paylaterInstallment = unzerInstance.PaylaterInstallment();

    var hpDimmer = document.getElementById('pit-dimmer-holder')
    var continueButton = document.getElementById('continue-button')
    var form = document.getElementById('payment-form-paylater-installment')

    paylaterInstallment.addEventListener('paylaterInstallmentEvent', function (e) {
        switch (e.currentStep) {
            case 'plan-list':
                continueButton.setAttribute('style', 'display: none')
                $('#error-holder').html('')
                break;

            case 'plan-detail':
                continueButton.setAttribute('style', 'display: block')
                $('#error-holder').html('')
                break;

            default:
                break;
        }

        if (e.action === 'validate' && e.success) {
            continueButton.removeAttribute('disabled')
        } else if (e.action === 'validate' && !e.success) {
            continueButton.setAttribute('disabled', true)
        }
    })

    let orderAmount = "99.99";
    paylaterInstallment.create({
        containerId: 'example-paylater-installment',
        amount: orderAmount,
        currency: 'EUR', // 'CHF'
        country: 'DE', // 'AT', 'CH'
        customerType: 'B2C', // 'B2B'
    })
        .then(function (data) {
            hpDimmer.setAttribute('style', 'display: none')
        })
        .catch(function (error) {
            console.log('create error', error)
            hpDimmer.setAttribute('style', 'display: none')
            $('#error-holder').html(error.customerMessage || error.message || 'Error')
        })

    // Handling the form's submission.
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        paylaterInstallment.createResource()
            .then(function (data) {
                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'paymentTypeId');
                hiddenInput.setAttribute('value', data.id);
                let amountInput = document.createElement('input');
                amountInput.setAttribute('type', 'hidden');
                amountInput.setAttribute('name', 'orderAmount');
                amountInput.setAttribute('value', orderAmount);
                form.appendChild(hiddenInput);
                form.appendChild(amountInput);
                form.setAttribute('method', 'POST');
                form.setAttribute('action', '<?php echo CONTROLLER_URL; ?>');
                form.submit();
            })
            .catch(function (error) {
                $('#error-holder').html(error.message)
            });
    });
</script>

</body>
</html>
