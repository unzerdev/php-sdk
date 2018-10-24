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

if (!isset($_POST['paymentTypeId'])) {
    returnError('PaymentType id is missing!');
}
$paymentTypeId   = $_POST['paymentTypeId'];

header('Content-Type: application/json');

try {
    $heidelpay     = new Heidelpay(PRIVATE_KEY);
    $authorization = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, AUTH_CONTROLLER_URL);
    $response[] = [
        'result' => 'success',
        'message' => 'Authorization ' . $authorization->getId() . ' has been created for payment ' . $authorization->getPaymentId() . '.'
    ];

    $charge = $authorization->charge();
    $response[] = [
        'result' => 'success',
        'message' => 'Charge ' . $charge->getId() . ' has been created for Authorization '. $authorization->getId() . ' of payment ' . $authorization->getPaymentId() . '.'
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
