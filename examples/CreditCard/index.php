<?php
/**
 * This file provides an example implementation of the credit card payment type.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';
?>

<!DOCTYPE html>
<html>

<?php include '../assets/partials/_indexPage_html.php'; ?>

<body>
    <div class="ui container">
        <div class="ui styled fluid accordion">
            <div class="title">
                <i class="dropdown icon"></i>
                Test Credit Card data
            </div>
            <div class="content">
                <p>Please use the following test data with this example. Refer to our <a href="https://docs.heidelpay.com/docs/testdata" target="_blank">documentation</a> for additional test data.</p>
                <table class="ui celled table">
                    <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Number</th>
                        <th>Expiration Date</th>
                        <th>CVC</th>
                        <th>Success</th>
                    </tr></thead>
                    <tbody>
                    <tr class="positive">
                        <td data-label="Brand">VISA</td>
                        <td data-label="Number">4711100000000000</td>
                        <td data-label="Expiration Date">Date in the future</td>
                        <td data-label="CVC">123</td>
                        <td class="center aligned">
                            <i class="large green checkmark icon"></i>
                        </td>
                    </tr>
                    <tr class="positive">
                        <td data-label="Brand">Mastercard</td>
                        <td data-label="Number">5453010000059543</td>
                        <td data-label="Expiration Date">Date in the future</td>
                        <td data-label="CVC">123</td>
                        <td class="center aligned">
                            <i class="large green checkmark icon"></i>
                        </td>
                    </tr>
                    <tr class="positive">
                        <td data-label="Brand">American Express</td>
                        <td data-label="Number">375000000000007</td>
                        <td data-label="Expiration Date">Date in the future</td>
                        <td data-label="CVC">1234</td>
                        <td class="center aligned">
                            <i class="large green checkmark icon"></i>
                        </td>
                    </tr>
                    <tr class="negative">
                        <td data-label="Brand">VISA</td>
                        <td data-label="Number">4644400000308888</td>
                        <td data-label="Expiration Date">Date in the future</td>
                        <td data-label="CVC">123</td>
                        <td class="center aligned">
                            <i class="large red x icon"></i>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="ui container segment">
        <div id="dimmer-holder" class="ui active dimmer" style="display: none;">
            <div class="ui loader"></div>
        </div>

        <!-- #######  1. Create a payment form. #####################################################################-->
        <form id="payment-form" class="heidelpayUI form" novalidate>

            <!-- #######  2. Add the necessary fields. ##############################################################-->
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

            <!-- #######  3. Add an error holder. ###################################################################-->
            <p id="error-holder" style="color: #9f3a38"></p>

            <div class="ui container segment">
                <div class="ui medium header">Normal use cases:</div>
                <div class="ui two column grid">
                    <div class="row">
                        <div class="twelve wide column">This example will perform the reservation of an amount from the given card. The amount can be charged later on e.g. before shipment. After authorization the customer will be redirected to the success or failure page.</div>
                        <div class="four wide column">
                            <button class="ui fluid primary button transaction" transaction="authorization">Authorize</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="twelve wide column">This example will charge an amount from the given card directly. After charge the customer will be redirected to the success or failure page.</div>
                        <div class="four wide column">
                            <button class="ui fluid primary button transaction" transaction="charge">Charge</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ui container segment">
                <div class="ui medium header">Extended examples:</div>
                <div class="ui two column grid">
                    <div class="row">
                        <div class="twelve wide column">This example will perform an authorization, cancel part of it and then charge the remaining amount of the authorization.</div>
                        <div class="four wide column">
                            <button class="ui fluid primary button transaction" transaction="authorizeReversal">Authorize with Reversal</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="twelve wide column">This example will perform a charge and cancel part of it.</div>
                        <div class="four wide column">
                            <button class="ui fluid primary button transaction" transaction="chargeCancel">Charge with Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="ui container messages">
    </div>

    <!-- #######  4. Initialize the form and add the functionality. #################################################-->
    <script>
        //#######  4.a Create the heidelpay object using your public key. ##############################################
        var heidelpayObj = new heidelpay(<?php echo '\'' . EXAMPLE_PUBLIC_KEY . '\''?>);

        //#######  4.b Create a card object to use for the payment.     ################################################
        //#######      And add map the fields from your form.           ################################################
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

        //#######  4.c Add a listener to react to changes in the fields. ###############################################
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

        //#######  4.d Handle the Form submit. #########################################################################
        //#######      In this case we have different buttons to show different use-cases. #############################
        //#######      In your case you will probably just handle the form submit. #####################################
        $(".transaction").click(
            function (event) {
                event.preventDefault();
                let $button = $(this);
                let url = '';

                switch ($button.attr("transaction")) {
                    case 'authorization':
                        url = '<?php echo AUTH_CONTROLLER_URL; ?>';
                        break;
                    case 'charge':
                        url = '<?php echo CHARGE_CONTROLLER_URL; ?>';
                        break;
                    case 'authorizeReversal':
                        url = '<?php echo AUTH_REVERSAL_CONTROLLER_URL; ?>';
                        break;
                    case 'chargeCancel':
                        url = '<?php echo CHARGE_CANCEL_CONTROLLER_URL; ?>';
                        break;
                    default:
                        logError('Unknown paymentType');
                        return;
                }

                showDimmerLoader();

                //#######  4.e Create the card resource. ###############################################################
                Card.createResource()
                    .then(function (data) {
                        logSuccess('PaymentType ' + data.id + ' has been successfully created.');

                        //#######  4.f And post the card id to your shop. ##############################################
                        $.ajax(
                            {
                                type: 'POST',
                                url: url,
                                success: function (result) {
                                    handleResponseJson(result);
                                    showDimmerMessage('Reload Page to perform a new request');
                                },
                                error: function (result) {
                                    handleResponseJson(result.responseText);
                                    showDimmerMessage('Reload Page to perform a new request');
                                },
                                data: {'paymentTypeId': data.id},
                                dataType: 'text'
                            }
                        );
                    })
                    .catch(function (error) {
                        hideDimmer()
                        errorMessage = error.customerMessage;
                        if (errorMessage === undefined) {
                            errorMessage = error.message;
                        }
                        document.getElementById('error-holder').innerHTML = errorMessage || error.message || 'Error';
                    });
            });
    </script>
    <?php include '../assets/partials/_indexPage_scripts.php'; ?>
</body>
</html>
