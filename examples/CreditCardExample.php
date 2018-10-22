<?php
/**
 * This file provides an example implementation of the credit card payment method.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/examples
 */
 
 ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>
        Heidelpay UI Examples
    </title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />
    <link rel="stylesheet" href="https://dev-static.heidelpay.com/v1/heidelpay.css" />
    <script type="text/javascript" src="https://dev-static.heidelpay.com/v1/heidelpay.js"></script>
</head>

<body style="padding: 70px 0 0">
<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>

<div class="ui container segment">
    <div id="dimmer-holder" class="ui active dimmer" style="display: none;">
        <div class="ui loader"></div>
    </div>
    <form id="payment-form" class="ui form" novalidate>
        <div class="field">
            <label for="example-card-number">Card Number</label>
            <span id="card-brand"></span>
            <div id="heidelpay-i-card-number"></div>
        </div>
        <div class="two fields">
            <div class="field">
                <label for="example-card-expiry-date">Expiry Date</label>
                <div id="heidelpay-i-card-expiry"></div>
            </div>
            <div class="field">
                <label id="label-card-ccv" for="example-card-ccv">CVC</label>
                <div id="heidelpay-i-card-cvc"></div>
            </div>
        </div>

        <div id="error-holder" class="field" style="color: #9f3a38"></div>

        <div class="field">
            <button class="ui primary button" type="submit">Pay</button>
        </div>
    </form>
</div>

<script>
    let heidelpay = new heidelpay('s-pub-uM8yNmBNcs1GGdwAL4ytebYA4HErD22H');

    // Credit Card example
    let Card = heidelpay.Card();
    Card.create('number', {
        containerId: 'heidelpay-i-card-number',
        // iconColor: '#0000FF',
        // iconPosition: 'right'
    });
    Card.create('expiry', {
        containerId: 'heidelpay-i-card-expiry',
        // iconColor: '#0000FF',
        // iconPosition: 'right'
    });
    Card.create('cvc', {
        containerId: 'heidelpay-i-card-cvc',
        // iconColor: '#0000FF',
        // iconPosition: 'right'
    });

    Card.listen('change', function (e) {
        // error handling
        if (e.success === false && e.error) {
            document.getElementById('error-holder').innerHTML =
                '<div class="ui negative message"><p>' + e.error + '</p></div>';
        } else if (e.success) {
            document.getElementById('error-holder').innerHTML = '';
        }
    });

    // // Handle card form submission.
    let form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        document.getElementById('dimmer-holder').style.display = 'block';
        Card.createResource()
            .then(function (data) {
                let input = document.createElement('input');
                input.name = 'token';
                input.type = 'hidden';
                input.value = data.id;
                console.log("Payment Id:" + data.id);
                form.appendChild(input);
            })
            .catch(function (error) {
                document.getElementById('error-holder').innerHTML =
                    '<div class="ui negative message"><p>' + error.error + '</p></div>'
            })
            .finally(function () {
                document.getElementById('dimmer-holder').style.display = 'none';
            })
    });
</script>
</body>

</html>
