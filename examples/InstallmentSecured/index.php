<?php
/**
 * This file provides an example implementation of the Installment Secured direct debit payment type.
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
    <meta charset="UTF-8"/>
    <title>Unzer UI Examples</title>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css"/>
    <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
</head>

<body style="margin: 70px 70px 0;">

<p><a href="https://docs.unzer.com/reference/test-data" target="_blank">Click here to open our test data in new tab.</a><br/></p>

<form id="payment-form-installmentsecured" class="unzerUI form unzerUI-installmentsecured__form" novalidate>
    <div id="example-installment-secured">
        <!-- The Installment Secured field UI Element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none" disabled>
        Continue
    </button>
</form>

<script>
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');

    let InstallmentSecured = unzerInstance.InstallmentSecured();

    InstallmentSecured.create({
        containerId: 'example-installment-secured', // required
        amount: 119.0, // required
        currency: 'EUR', // required
        effectiveInterest: 4.5, // required
        orderDate: '2019-04-18', // optional
    })
        .then(function(data){
            // if successful, notify the user that the list of installments was fetched successfully
            // in case you were using a loading element during the fetching process,
            // you can remove it inside this callback function
        })
        .catch(function(error) {
            // sent an error message to the user (fetching installment list failed)
        });



    let continueButton = document.getElementById('continue-button');

    InstallmentSecured.addEventListener('installmentSecuredEvent', function(e) {
        if (e.action === 'validate') {
            if (e.success) {
                continueButton.removeAttribute('disabled')
            } else {
                continueButton.setAttribute('disabled', 'true')
            }
        }

        if (e.action === 'change-step') {
            if (e.currentStep === 'plan-list') {
                continueButton.setAttribute('style', 'display: none')
            } else {
                continueButton.setAttribute('style', 'display: block')
            }
        }
    });

    // Handling the form's submission.
    let form = document.getElementById('payment-form-installmentsecured');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        InstallmentSecured.createResource()
            .then(function(data) {
                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'paymentTypeId');
                hiddenInput.setAttribute('value', data.id);
                form.appendChild(hiddenInput);
                form.setAttribute('method', 'POST');
                form.setAttribute('action', '<?php echo CONTROLLER_URL; ?>');

                form.submit();
            })
            .catch(function(error) {
                $('#error-holder').html(error.message)
            });
    });
</script>

</body>
</html>
