<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Heidelpay;

//#######   Checks whether examples are enabled. #######################################################################
require_once __DIR__ . '/CardConstants.php';

/**
 * Require the composer autoloader file
 */
require_once __DIR__ . '/../../../autoload.php';


if (!isset($_POST['paymentTypeId'])) {
    throw new RuntimeException('PaymentType id is missing!');
}
if (!isset($_POST['transaction'])) {
    throw new RuntimeException('Transaction is missing!');
}

$paymentTypeId   = $_POST['paymentTypeId'];
$transactionType = $_POST['transaction'];
$transaction = null;

try {
    $heidelpay = new Heidelpay(PRIVATE_KEY);
    switch ($transactionType) {
        case 'authorization':
            $transaction = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, CONTROLLER_URL);
            break;
        case 'charge':
            $transaction = $heidelpay->charge(100.0, Currencies::EURO, $paymentTypeId, CONTROLLER_URL);
            break;
        default:
            throw new RuntimeException('Transaction type ' . $transactionType . ' is unknown!');
            break;
    }

    returnMessage(ucfirst($transactionType) . ' has been created for payment ' . $transaction->getPaymentId() . '.');

} catch (RuntimeException $e) {
    returnError($e->getMessage());
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException $e) {
    returnError($e->getClientMessage());
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException $e) {
    returnError($e->getClientMessage());
}

function returnError($message) {
    header('HTTP/1.1 500 Internal Server Error');
    echo($message);
}

function returnMessage($message) {
    echo $message;
}
