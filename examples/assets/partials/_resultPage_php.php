<?php
/**
 * This is the php partial for the result pages (success/failure).
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

use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;

session_start();

/** Require the constants of this example */
$examplePath = $_SESSION['examplePath'];
require_once $examplePath . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../../autoload.php';

if (!isset($_SESSION['paymentId'])) {
    echo 'PaymentId is missing!';
    die;
}
$paymentId = $_SESSION['paymentId'];

/** @var Heidelpay $heidelpay */
$heidelpay     = new Heidelpay(EXAMPLE_PRIVATE_KEY);
$payment = $heidelpay->fetchPayment($paymentId);

/**
 * @param AbstractTransactionType $transaction
 * @return string
 */
function renderTransactionMetaData($transaction)
{
    return
        '<ul>' .
        '<li>Id: ' . $transaction->getId() . '</li>' .
        '<li>ShortId: ' . $transaction->getShortId() . '</li>' .
        '<li>UniqueId: ' . $transaction->getUniqueId() . '</li>' .
        '</ul>';
}

function renderPaymentDetails(Payment $payment)
{
    $authorization = $payment->getAuthorization();

    $transactionHtml = $authorization instanceof Authorization ?
        '<li>Authorization:</li>' . renderTransactionMetaData($authorization) : '';

    /** @var Charge $charge */
    foreach ($payment->getCharges() as $charge) {
        $transactionHtml .= '<li>Charge:</li>' . renderTransactionMetaData($payment->getCharge($charge->getId()));
    }

    /** @var Cancellation $cancellation */
    foreach ($payment->getCancellations() as $cancellation) {
        $transactionHtml .= '<li>Cancellation:</li>' . renderTransactionMetaData($payment->getCancellation($cancellation->getId()));
    }

    /** @var Shipment $shipment */
    foreach ($payment->getShipments() as $shipment) {
        $transactionHtml .= '<li>Shipment:</li>' . renderTransactionMetaData($payment->getShipment($shipment->getId()));
    }

    return
        '<p>Payment Details:</p>' .
        '<ul class="ui list">' .
        '<li>Id:' . $payment->getId() . '</li>' .
        '<li>Transactions:' .
        '<ul>' .
        $transactionHtml .
        '</ul>' .
        '</li>' .
        '<li>Status:' . $payment->getStateName() . '</li>' .
        '</ul>';
}
