<?php
/**
 * This file provides an example implementation of the SEPA direct debit secured payment type.
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
</head>

<body style="margin: 70px 70px 0;">

<p><a href="https://docs.unzer.com/reference/test-data" target="_blank">Click here to open our test data in new tab.</a></p>

<form id="payment-form">
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <div id="customer" class="field">
        <!-- The customer form UI element will be inserted here -->
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"></div>
    <button class="unzerUI primary button fluid" id="submit-button" type="submit">Pay</button>
</form>

<script>
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');

    // Create a SEPA Direct Debit Secured instance and render the form
    let SepaDirectDebitSecured = unzerInstance.SepaDirectDebitSecured();
    SepaDirectDebitSecured.create('sepa-direct-debit-secured', {
        containerId: 'sepa-secured-IBAN'
    });

    // Creat a customer instance and render the form
    let Customer = unzerInstance.Customer();
    Customer.create({
        containerId: 'customer'
    });

    // Handle payment form submission.
    let form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let sepaDirectDebitSecuredPromise = SepaDirectDebitSecured.createResource();
        let customerPromise = Customer.createCustomer();
        Promise.all([sepaDirectDebitSecuredPromise, customerPromise])
            .then(function(values) {
                let paymentType = values[0];
                let customer = values[1];
                let hiddenInputPaymentTypeId = document.createElement('input');
                hiddenInputPaymentTypeId.setAttribute('type', 'hidden');
                hiddenInputPaymentTypeId.setAttribute('name', 'paymentTypeId');
                hiddenInputPaymentTypeId.setAttribute('value', paymentType.id);
                form.appendChild(hiddenInputPaymentTypeId);

                let hiddenInputCustomerId = document.createElement('input');
                hiddenInputCustomerId.setAttribute('type', 'hidden');
                hiddenInputCustomerId.setAttribute('name', 'customerId');
                hiddenInputCustomerId.setAttribute('value', customer.id);
                form.appendChild(hiddenInputCustomerId);

                form.setAttribute('method', 'POST');
                form.setAttribute('action', '<?php echo CONTROLLER_URL; ?>');

                // Submitting the form
                form.submit();
            })
            .catch(function(error) {
                $('#error-holder').html(error.message)
            })
    });
</script>

</body>
</html>
