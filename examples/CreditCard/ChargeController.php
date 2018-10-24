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
require_once __DIR__ . '/Constants.php';

/**
 * Require the composer autoloader file
 */
require_once __DIR__ . '/../../../../autoload.php';

if (!isset($_POST['paymentTypeId'])) {
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

header('Content-Type: application/json');

try {
    $heidelpay  = new Heidelpay(PRIVATE_KEY);
    $charge     = $heidelpay->charge(100.0, Currencies::EURO, $paymentTypeId, CHARGE_CONTROLLER_URL);

    $response[] = [
        'result' => 'success',
        'message' => 'Charge ' . $charge->getId() . ' has been created for payment ' . $charge->getPaymentId() . '.'
    ];

} catch (RuntimeException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getMessage()];
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getClientMessage()];
} catch (\heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    $response[] = ['result' => 'error', 'message' => $e->getClientMessage()];
}

echo json_encode($response);
