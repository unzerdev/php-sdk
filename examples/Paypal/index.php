<?php
/**
 * This file provides an example implementation of the Paypal payment type.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
                Paypal Test data
            </div>
            <div class="content">
                <p>Please use the following test data with this example. Refer to our <a href="https://docs.heidelpay.com/docs/testdata" target="_blank">documentation</a> for additional test data.</p>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Pay with</th>
                            <th>Success</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="positive">
                            <td data-label="Username">paypal-customer@heidelpay.de</td>
                            <td data-label="Password">heidelpay</td>
                            <td data-label="Pay with">MasterCard x-9675</td>
                            <td class="center aligned">
                                <i class="large green checkmark icon"></i>
                            </td>
                        </tr>
                        <tr class="negative">
                            <td data-label="Username">paypal-customer@heidelpay.de</td>
                            <td data-label="Password">heidelpay</td>
                            <td data-label="Pay with">KBC Bank NV x-1231</td>
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

        <div class="ui container segment">
            <div class="ui medium header">Normal use cases:</div>
            <div class="ui two column grid">
                <div class="row">
                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will perform the reservation of an amount. The amount can be charged later on e.g. before shipment. After authorization the customer will be redirected to the success or failure page.</div>
                    <div class="four wide column">
                        <button class="ui fluid primary button transaction" transaction="authorization" disabled>Authorize</button>
                    </div>
                </div>
                <div class="row">
                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will charge an amount directly. After charge the customer will be redirected to the success or failure page.</div>
                    <div class="four wide column">
                        <button class="ui fluid primary button transaction" transaction="charge">Charge</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui container messages">
    </div>

    <!-- #######  4. Initialize the form and add the functionality. #################################################-->
    <script>
        //#######  4.a Create the heidelpay object using your public key. ##############################################
        var heidelpayObj = new heidelpay(<?php echo '\'' . EXAMPLE_PUBLIC_KEY . '\''?>);

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
    </script>
    <?php include '../assets/partials/_indexPage_scripts.php'; ?>
</body>
</html>
