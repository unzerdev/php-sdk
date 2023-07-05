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
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css"/>
</head>
<body style="margin: 30px 70px 0;">
<div class="ui container segment spaced">
    <h1 id="result" class="ui header">Pending</h1>
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
    <a href="." class="ui green button">start again</a>
</div>
</body>
</html>
