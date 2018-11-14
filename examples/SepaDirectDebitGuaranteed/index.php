<?php
/**
 * This file provides an example implementation of the Paypal payment type.
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
                Sepa Direct Debit Guaranteed Test data
            </div>
            <div class="content">
                <p>Please use the following test data with this example. Refer to our <a href="https://docs.heidelpay.com/docs/testdata" target="_blank">documentation</a> for additional test data.</p>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>IBAN</th>
                            <th>Customer</th>
                            <th>Birthday</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th>Success</th>
                        </tr>
                    </thead>
                    <tbody style="vertical-align: top;">
                        <tr class="positive">
                            <td data-label="Country" style="">Germany</td>
                            <td data-label="IBAN">DE89370400440532013000</td>
                            <td data-label="Customer">Mr Peter Universum</td>
                            <td data-label="Birthday">1989-12-24</td>
                            <td data-label="Address">Hugo-Junkers-Str. 5<br>60386 Frankfurt am Main<br>Germany</td>
                            <td data-label="Email">peter.universum@universum-group.de</td>
                            <td class="center aligned">
                                <i class="large green checkmark icon"></i>
                            </td>
                        </tr>
                        <tr class="positive">
                            <td data-label="Country">Austria</td>
                            <td data-label="IBAN">AT591400000000123456</td>
                            <td data-label="Customer">Mr Peter Universum</td>
                            <td data-label="Birthday">1989-12-24</td>
                            <td data-label="Address">Stumpergasse 27<br>1060 Wien<br>Austria</td>
                            <td data-label="Email">peter.universum@universum-group.de</td>
                            <td class="center aligned">
                                <i class="large green checkmark icon"></i>
                            </td>
                        </tr>
                        <tr class="negative">
                            <td data-label="Country">Germany</td>
                            <td data-label="IBAN">DE89370400440532013000</td>
                            <td data-label="Customer">Mr Peter Lausig</td>
                            <td data-label="Birthday">1989-12-24</td>
                            <td data-label="Address">Hugo-Junkers-Str. 5<br>60386 Frankfurt am Main<br>Germany</td>
                            <td data-label="Email">peter.universum@universum-group.de</td>
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
        <form id="payment-form" class="ui form" novalidate>
            <div id="heidelpay-e-sepa-direct-debit-guaranteed" class="field"></div>

            <div id="error-holder" class="field" style="color: #9f3a38"></div>

            <div class="field">
                <button class="ui primary button" type="submit">Pay</button>
            </div>
        </form>

<!--        <div class="ui container segment">-->
<!--            <div class="ui medium header">Normal use cases:</div>-->
<!--            <div class="ui two column grid">-->
<!--                <div class="row">-->
<!--                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will perform the reservation of an amount. The amount can be charged later on e.g. before shipment. After authorization the customer will be redirected to the success or failure page.</div>-->
<!--                    <div class="four wide column">-->
<!--                        <button class="ui fluid primary button transaction" transaction="authorization" disabled>Authorize</button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="row">-->
<!--                    <div class="twelve wide column">This example will redirect the customer to the Paypal page and will charge an amount directly. After charge the customer will be redirected to the success or failure page.</div>-->
<!--                    <div class="four wide column">-->
<!--                        <button class="ui fluid primary button transaction" transaction="charge">Charge</button>-->
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
        var heidelpayObj = new heidelpay(<?php echo '\'' . EXAMPLE_PUBLIC_KEY . '\''?>);

        //#######  4.b Create a card object to use for the payment.     ################################################
        //#######      And add map the fields from your form.           ################################################
        var Sepa = heidelpayObj.SepaDirectDebit();
        Sepa.initFormFields({
            "lastname": "Lausig",
            "firstname": "Peter",
            "salutation": "mr",
            "birthDate": "1989-12-24",
            "email": "peter.universum@universum-group.de",
            "address": {
                "name": "Peter Lausig",
                "street": "Hugo-Junkers-Str. 5",
                "state": "DE-HE",
                "zip": "60386",
                "city": "Frankfurt am Main",
                "country": "DE"
            }
        });
        Sepa.create('sepa-direct-debit-guaranteed', {
            containerId: 'heidelpay-e-sepa-direct-debit-guaranteed',
            errorHolderId: 'error-holder',
        });

        //#######  4.d Handle the Form submit. #########################################################################
        let form = document.getElementById('payment-form');
        form.addEventListener('submit', function (event) {
                event.preventDefault();

                showDimmerLoader();

                Sepa.createResource()
                    .then(function (data) {
                        document.getElementById('dimmer-holder').innerHTML
                            = '<div style="color: #eee;top: 43%;position: relative;" class="ui">Resource Id: ' + data.id + '</div>'
                    })
                    .catch(function (error) {
                        document.getElementById('dimmer-holder').style.display = 'none';
                        document.getElementById('error-holder').innerHTML = error.customerMessage || error.message || 'Error'
                    });

                ////#######  4.e Create the card resource. ###############################################################
                //Sepa.createResource()
                //    .then(function (data) {
                //        logSuccess('PaymentType ' + data.id + ' has been successfully created.');
                //
                //        //#######  4.f And post the card id to your shop. ##############################################
                //        $.ajax(
                //            {
                //                type: 'POST',
                //                url: '<?php //echo CHARGE_CONTROLLER_URL; ?>//',
                //                success: function (result) {
                //                    handleResponseJson(result);
                //                    showDimmerMessage('Reload Page to perform a new request');
                //                },
                //                error: function (result) {
                //                    handleResponseJson(result.responseText);
                //                    showDimmerMessage('Reload Page to perform a new request');
                //                },
                //                data: {'paymentTypeId': data.id},
                //                dataType: 'text'
                //            }
                //        );
                //    })
                //    .catch(function (error) {
                //        hideDimmer()
                //        errorMessage = error.customerMessage;
                //        if (errorMessage === undefined) {
                //            errorMessage = error.message;
                //        }
                //        document.getElementById('error-holder').innerHTML = errorMessage || error.message || 'Error';
                //    });
            });
    </script>
    <?php include '../assets/partials/_indexPage_scripts.php'; ?>
</body>
</html>
