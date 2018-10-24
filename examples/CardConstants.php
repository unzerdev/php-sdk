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

require_once __DIR__ . '/_enableExamples.php';
if (defined('HEIDELPAY_PHP_PAYMENT_API_EXAMPLES') && HEIDELPAY_PHP_PAYMENT_API_EXAMPLES !== true) {
    exit();
}

const EXAMPLE_BASE_FOLDER = HEIDELPAY_PHP_PAYMENT_API_URL . HEIDELPAY_PHP_PAYMENT_API_FOLDER;
define('CONTROLLER_URL', EXAMPLE_BASE_FOLDER . 'CardController.php');
define('RESPONSE_URL', EXAMPLE_BASE_FOLDER . 'CardController.php');
define('RESPONSE_FILE_NAME', __DIR__ . '/ResponseParams.txt');
define('HEIDELPAY_SUCCESS_PAGE', EXAMPLE_BASE_FOLDER . 'HeidelpaySuccess.php');
define('HEIDELPAY_FAILURE_PAGE', EXAMPLE_BASE_FOLDER . 'HeidelpayError.php');
define('PUBLIC_KEY', 's-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa');
define('PRIVATE_KEY', 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');