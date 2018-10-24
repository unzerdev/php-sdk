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
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

try {
    $heidelpay = new Heidelpay(PRIVATE_KEY);
    $charge    = $heidelpay->charge(100.0, Currencies::EURO, $paymentTypeId, AUTH_CONTROLLER_URL);
    echo 'Charge ' . $charge->getId() . ' has been created for payment ' . $charge->getPaymentId() . '.';

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
    die();
}
