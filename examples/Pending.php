<?php
/**
 * This is the pending page for the example payments.
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

session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <body>
        <h1 id="result">Pending</h1>
        <p>
            The payment transaction has been completed, however it has the state pending.<br>
            The status of the payment is not definite at the moment.<br>
            You can create the Order in your shop but should set its status to <i>pending payment</i>.
        </p>
        <p>
            Please use the webhook feature to be informed about later changes of the payment.
            You should ship only if the status changes to success.
            <?php
            if (isset($_SESSION['ShortId']) && !empty($_SESSION['ShortId'])) {
                echo '<p>Please look for ShortId ' . $_SESSION['ShortId'] . ' in Unzer Insights to see the transaction.</p>';
            }
            if (isset($_SESSION['PaymentId']) && !empty($_SESSION['PaymentId'])) {
                echo '<p>The PaymentId of your transaction is \'' . $_SESSION['PaymentId'] . '\'.</p>';
            }
            ?>
        </p>
        <p><a href=".">start again</a></p>
    </body>
</html>
