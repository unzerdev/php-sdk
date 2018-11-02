<?php
/**
 * This file provides an example implementation of the Paypal payment type.
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
require_once __DIR__ . '/Constants.php';

//#######   User the composer autoloader. ##############################################################################
require_once __DIR__ . '/../../../../autoload.php';

session_start();
session_unset();
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

    <script type="text/javascript" src="<?php echo EXAMPLE_BASE_FOLDER . 'js/logger.js' ?>"></script>
    <script type="text/javascript" src="<?php echo EXAMPLE_BASE_FOLDER . 'js/dimmer.js' ?>"></script>
    <script type="text/javascript" src="<?php echo EXAMPLE_BASE_FOLDER . 'js/cookieHandler.js' ?>"></script>

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
    <div class="ui container">
        <!--<div class="ui styled fluid accordion">
            <div class="title">
                <i class="dropdown icon"></i>
                Test PayPal data
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
                    </tbody>
                </table>
            </div>
        </div>-->

    </div>
    <div class="ui container segment">
        <div id="dimmer-holder" class="ui active dimmer" style="display: none;">
            <div class="ui loader"></div>
        </div>

        <div class="ui container segment">
            <div class="ui medium header">Normal use cases:</div>
            <div class="ui two column grid">
<!--                <div class="row">-->
<!--                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will perform the reservation of an amount. The amount can be charged later on e.g. before shipment. After authorization the customer will be redirected to the success or failure page.</div>-->
<!--                    <div class="four wide column">-->
<!--                        <button class="ui fluid primary button transaction" transaction="authorization">Authorize</button>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="row">
                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will charge an amount directly. After charge the customer will be redirected to the success or failure page.</div>
                    <div class="four wide column">
                        <button class="ui fluid primary button transaction" transaction="charge">Charge</button>
                    </div>
                </div>
            </div>
        </div>

<!--        <div class="ui container segment">-->
<!--            <div class="ui medium header">Extended examples:</div>-->
<!--            <div class="ui two column grid">-->
<!--                <div class="row">-->
<!--                    <div class="twelve wide column">This example will perform an authorization, cancel part of it and then charge the remaining amount of the authorization.</div>-->
<!--                    <div class="four wide column">-->
<!--                        <button class="ui fluid primary button transaction" transaction="authorizeReversal" disabled>Authorize with Reversal</button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="row">-->
<!--                    <div class="twelve wide column">This example will perform a charge and cancel part of it.</div>-->
<!--                    <div class="four wide column">-->
<!--                        <button class="ui fluid primary button transaction" transaction="chargeCancel" disabled>Charge with Cancel</button>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
    </div>

    <div class="ui container messages">
    </div>

    <!-- #######  4. Initialize the form and add the functionality. #################################################-->
    <script>
        //#######  4.a Create the heidelpay object using your public key. ##############################################
        var heidelpayObj = new heidelpay(<?php echo '\''.PUBLIC_KEY . '\''?>);

        //#######  4.b Create a card object to use for the payment.     ################################################
        //#######      And add map the fields from your form.           ################################################
        var paypal = heidelpayObj.Paypal();

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
                paypal.createResource()
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

        $('.ui.accordion')
            .accordion()
        ;

        function handleResponseJson(response) {
            JSON.parse(response).forEach(function(item) {

                setCookie('PaymentId', item['paymentId']);
                switch(item['result']) {
                    case 'success':
                        logSuccess(item['message']);
                        break;
                    case 'info':
                        logInfo(item['message']);
                        break;
                    case 'redirect':
                        let url = item['redirectUrl'];
                        if (item['paymentId'] !== undefined) {
                            url = url + '?paymentid=' + item['paymentId'];
                        }
                        window.location.href = url;
                        break;
                    default:
                        logError(item['message']);
                        break;
                }
            })
        }

    </script>
</body>

</html>
