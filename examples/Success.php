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
 * @package  UnzerSDK\examples
 */

use UnzerSDK\Unzer;

require_once __DIR__ . '/Constants.php';
require_once __DIR__ . '/../../../autoload.php';

session_start();

$additionalPaymentInformation = $_SESSION['additionalPaymentInformation'] ?? null;
$shortId = $_SESSION['ShortId'] ?? null;
$paymentId = $_SESSION['PaymentId'] ?? null;
$paymentTypeId = $_SESSION['PaymentTypeId'] ?? null;
$isAuthorizeTransaction = $_SESSION['isAuthorizeTransaction'] ?? false;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unzer UI Examples</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css"/>

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css"/>
</head>
<body style="margin: 30px 70px 0;">
<div class="ui container segment">

    <h1 id="result" class="ui header">Success</h1>
    <p>The order has been successfully placed.</p>

        <?php
        if (!empty($additionalPaymentInformation)) {
            echo $additionalPaymentInformation;
        }

        if ($shortId !== null) {
            $defaultTransactionMessage = '<p>Please look for ShortId ' . $shortId . ' in Unzer Insights to see the transaction.</p>';
            $paylaterTransactionMessage = '<p>Please use the "descriptor" to look for the transaction in the Unzer Pay Later Merchant Portal.</p>';
            echo preg_match('/[\d]{4}.[\d]{4}.[\d]{4}/', $shortId) ? $defaultTransactionMessage : $paylaterTransactionMessage;
        }

        $isManageable = false;
        if ($paymentId !== null) {
            echo '<p>The PaymentId of your transaction is \'' . $paymentId . '\'.</p>';
            $unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
            $payment = $unzer->fetchPayment($paymentId);
            $isManageable = $payment->getPaymentType()->supportsDirectPaymentCancel() || $payment->getAuthorization() !== null;
        }

        if ($paymentTypeId !== null) {
            echo '<p>The TypeId for the recurring payment is \'' . $paymentTypeId . '\'. You can use it
                            now for subsequent transactions.</p>
                            <form id="payment-form" class="unzerUI form" action="' . RECURRING_PAYMENT_CONTROLLER_URL . '" method="post">
                                <input type="hidden" name="payment_type_id" value="' . $paymentTypeId . ' ">
                                <div class="fields inline">
                                    <div class="field">
                                        <button class="unzerUI primary button fluid" id="submit-button" type="submit">Charge payment type again.</button>
                                    </div>
                                </div>
                            </form>';
        }

        if ($isManageable) {
            echo '<p>As a merchant you can charge or cancel the Payment here: <a href="./Backend/ManagePayment.php">Manage Payment</a></p>';
        }
        ?>
    <a href="." class="ui green button">start again</a>
</div>
</body>
</html>
