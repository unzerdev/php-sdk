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

//#######   Checks whether examples are enabled. #######################################################################
require_once __DIR__ . '/CardConstants.php';

 ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>
        Heidelpay UI Examples
    </title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />

    <link rel="stylesheet" href="https://static.heidelpay.com/v1/heidelpay.css" />
    <script type="text/javascript" src="https://static.heidelpay.com/v1/heidelpay.js"></script>
    <style>
        html, body {
            margin: 0;
            padding: 70px 0 0;
            height: 330px;
            min-width: initial;
        }
    </style>
</head>

<body>

    <div class="ui container segment">
        <div id="dimmer-holder" class="ui active dimmer" style="display: none;">
            <div class="ui loader"></div>
        </div>
        <form id="payment-form" class="heidelpayUI form" novalidate>
            <div class="field">
                <label for="card-number">Card Number</label>
                <div class="heidelpayUI left icon input">
                    <div id="heidelpay-i-card-number" class="heidelpayInput"></div>
                    <i id="card-icon" class="icon h-iconimg-card-default"></i>
                </div>
            </div>
            <div class="two fields unstackable">
                <div class="field">
                    <label for="card-expiry-date">Expiry Date</label>
                    <div class="heidelpayUI left icon input">
                        <div id="heidelpay-i-card-expiry" class="heidelpayInput"></div>
                        <i class="icon h-iconimg-card-expiry"></i>
                    </div>
                </div>
                <div class="field">
                    <label id="label-card-ccv" for="card-ccv">CVC</label>
                    <div class="heidelpayUI left icon input">
                        <div id="heidelpay-i-card-cvc" class="heidelpayInput"></div>
                        <i class="icon h-iconimg-card-cvc"></i>
                    </div>
                </div>
            </div>

            <p id="error-holder" style="color: #9f3a38"></p>
            <div class="field">
                <button class="ui primary button transaction" transaction="authorization">Authorize</button>
                <button class="ui primary button transaction" transaction="charge">Charge</button>
            </div>
        </form>
    </div>

    <div class="ui container messages">
    </div>

    <script>
        var heidelpayObj = new heidelpay(<?php echo '\''.PUBLIC_KEY . '\''?>);

        // Credit Card example
        var Card = heidelpayObj.Card();
        Card.create('number', {
            containerId: 'heidelpay-i-card-number',
        });
        Card.create('expiry', {
            containerId: 'heidelpay-i-card-expiry',
        });
        Card.create('cvc', {
            containerId: 'heidelpay-i-card-cvc',
        });

        Card.addEventListener('change', function (e) {
            if (e.cardType) {
                let $card = $('#card-icon');
                $card.removeClass();
                $card.addClass(`icon h-iconimg-${e.cardType.imgName}`)
            }

            // error handling
            var $inputElement = $(`#heidelpay-i-card-${e.type}`);
            var $icon = $inputElement.next();
            var $errorHolder = $('#error-holder');
            if (e.success === false && e.error) {
                $inputElement.closest('.heidelpayUI.input').addClass('error');
                $inputElement.closest('.field').addClass('error');
                $icon.addClass('h-iconimg-error');
                $errorHolder.html(e.error)
            } else if (e.success) {
                $inputElement.parent('.heidelpayUI.input').removeClass('error');
                $inputElement.closest('.field').removeClass('error');
                $icon.removeClass('h-iconimg-error');
                $errorHolder.html('')
            }
        });

        function logSuccess(message){
            logMessage(message, 'Success', 'green');
        }

        function logError(message){
            logMessage(message, 'Error', 'red');
        }

        function logMessage(message, title, color){
            var count = $('.messages .message').length;

            message =
                '<div class="ui ' + color + ' info message">' +
                // '<i class="close icon"></i>'+
                '<div class="header">' +
                (count + 1) + '. ' + title +
                '</div>' +
                message +
                '</div>';

            $('.messages').append(message);
        }

        function logResponseJson(response) {
            JSON.parse(response).forEach(function(item) {
                if (item['result'] === 'success') {
                    logSuccess(item['message']);
                } else {
                    logError(item['message']);
                }
            })
        }

        // Handle card form submission.
        $(".transaction").click(
            function (event) {
                event.preventDefault();
                $button = $(this);
                switch ($button.attr("transaction")) {
                    case 'authorization':
                        url = '<?php echo AUTH_CONTROLLER_URL; ?>';
                        break;
                    case 'charge':
                        url = '<?php echo CHARGE_CONTROLLER_URL; ?>';
                        break;
                    default:
                        logError('Unknown paymentType');
                        return;
                }

                document.getElementById('dimmer-holder').style.display = 'block';
                Card.createResource()
                    .then(function (data) {
                        logSuccess('PaymentType ' + data.id + ' has been successfully created.');
                        document.getElementById('dimmer-holder').innerHTML
                            = `<div style="color: #eee;top: 43%;position: relative;" class="ui">Reload Page to perform a new request</div>`;
                        $.ajax(
                            {
                                type: 'POST',
                                url: url,
                                success: function (result) {
                                    logResponseJson(result);
                                },
                                error: function (result) {
                                    logResponseJson(result.responseText);
                                },
                                data: {'paymentTypeId': data.id},
                                dataType: 'text'
                            }
                        );
                    })
                    .catch(function (error) {
                        document.getElementById('dimmer-holder').style.display = 'none';
                        errorMessage = error.customerMessage;
                        if (errorMessage === undefined) {
                            errorMessage = error.message;
                        }
                        document.getElementById('error-holder').innerHTML = errorMessage || error.message || 'Error';
                    })
            });
    </script>
</body>

</html>
