<?php
/**
 * This is the success page for the example payments.
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

require_once __DIR__ . '/Constants.php';

session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Unzer UI Examples</title>

        <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css" />
    </head>
    <body>
        <h1 id="result">Success</h1>
        <p>
            The order has been successfully placed.

            <?php
            if (isset($_SESSION['additionalPaymentInformation'])) {
                echo $_SESSION['additionalPaymentInformation'];
            }

            if (isset($_SESSION['ShortId']) && !empty($_SESSION['ShortId'])) {
                echo '<p>Please look for ShortId ' . $_SESSION['ShortId'] . ' in Unzer Insights to see the transaction.</p>';
            }
            if (isset($_SESSION['PaymentId']) && !empty($_SESSION['PaymentId'])) {
                echo '<p>The PaymentId of your transaction is \'' . $_SESSION['PaymentId'] . '\'.</p>';
            }
            if (isset($_SESSION['PaymentTypeId']) && !empty($_SESSION['PaymentTypeId'])) {
                echo    '<p>The TypeId for the recurring payment is \'' . $_SESSION['PaymentTypeId'] . '\'. You can use it
                            now for subsequent transactions.</p>
                            <form id="payment-form" class="unzerUI form" action="' . RECURRING_PAYMENT_CONTROLLER_URL . '" method="post">
                                <input type="hidden" name="payment_type_id" value="' . $_SESSION['PaymentTypeId'] . ' ">
                                <div class="fields inline">
                                    <div class="field">
                                        <button class="unzerUI primary button fluid" id="submit-button" type="submit">Charge payment type again.</button>
                                    </div>
                                </div>
                            </form>';
            }
            ?>
        </p>
        <p><a href=".">start again</a></p>
    </body>
</html>
