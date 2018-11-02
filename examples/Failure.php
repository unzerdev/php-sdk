<?php
/**
 * This is the success page for the example payments.
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
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;

//#######   Checks whether examples are enabled. #######################################################################
require_once __DIR__ . '/Constants.php';

/**
 * Require the composer autoloader file
 */
require_once __DIR__ . '/../../../autoload.php';

session_start();

if (!isset($_SESSION['paymentId'])) {
    throw new \RuntimeException('PaymentId is missing!');
}

$paymentId = $_SESSION['paymentId'];

/** @var Heidelpay $heidelpay */
$heidelpay     = new Heidelpay(PRIVATE_KEY);
$payment = $heidelpay->fetchPayment($paymentId);

/**
 * @param AbstractTransactionType $transaction
 * @return string
 */
function renderTransactionMetaData($transaction) {
    return
        '<ul>' .
        '<li>Id: ' . $transaction->getId() . '</li>' .
        '<li>ShortId: ' . $transaction->getShortId() . '</li>'.
        '<li>UniqueId: ' . $transaction->getUniqueId() . '</li>'.
        '</ul>';
}

function renderPaymentDetails(Payment $payment) {
    $authorization = $payment->getAuthorization();

    $transactionHtml = $authorization instanceof Authorization ?
        '<li>Authorization:</li>' . renderTransactionMetaData($authorization) : '';

    /** @var Charge $charge */
    foreach ($payment->getCharges() as $charge) {
        $transactionHtml .= '<li>Charge:</li>' . renderTransactionMetaData($payment->getChargeById($charge->getId()));
    }

    /** @var Cancellation $cancellation */
    foreach ($payment->getCancellations() as $cancellation) {
        $transactionHtml .= '<li>Cancellation:</li>' . renderTransactionMetaData($payment->getCancellation($cancellation->getId()));
    }

    /** @var Shipment $shipment */
    foreach ($payment->getShipments() as $shipment) {
        $transactionHtml .= '<li>Shipment:</li>' . renderTransactionMetaData($payment->getShipmentById($shipment->getId()));
    }

    return
        '<p>Payment Details:</p>' .
        '<ul class="ui list">' .
        '<li>Id:' . $payment->getId() . '</li>' .
        '<li>Transactions:' .
        '<ul>' .
        $transactionHtml .
        '</ul>'.
        '</li>' .
        '<li>Status:'. $payment->getStateName() . '</li>'.
        '</ul>';
}

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
            padding: 20px 0 0;
            height: 330px;
            min-width: initial;
        }
    </style>
</head>

<body>
<div class="ui container messages">

    <div class="ui red info message">
        <div class="header">
            Failure
        </div>
        <p>There has been an error completing the payment.</p>
        <?php
        echo renderPaymentDetails($payment);
        ?>

    </div>

    <a href="javascript:history.go(-1)">go back</a>
</div>
</body>

</html>
