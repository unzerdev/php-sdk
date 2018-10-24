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
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
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

$paymentTypeId = $_POST['paymentTypeId'];

try {
    $heidelpay = new Heidelpay( PRIVATE_KEY );
} catch (HeidelpaySdkException $e) {
    echo 'Error: ' . $e->getMessage();
    die;
}

try {
    $authorization = $heidelpay->authorize(100.0, Currencies::EURO, $paymentTypeId, CONTROLLER_URL);
} catch (RuntimeException $e) {
    // log $e->getMessage();
    echo 'Error: An unexpected error occurred';
    die;
} catch (HeidelpayApiException $e) {
    // log $e->getMessage();
    echo 'Error: ' . $e->getMessage();
    echo 'Error: ' . $e->getClientMessage();
    die;
} catch (HeidelpaySdkException $e) {
    // log $e->getMessage();
    echo 'Error: ' . $e->getMessage();
    echo 'Error: ' . $e->getClientMessage();
    die;
}

echo 'Success: Authorization has been created for Payment #' . $authorization->getPaymentId();
