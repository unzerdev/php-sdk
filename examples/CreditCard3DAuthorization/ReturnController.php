<?php
/**
 * This is the controller for the 'Authorization' transaction example for Card.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use heidelpayPHP\Constants\Currencies;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Customer;

function redirect($url)
{
    header('Location: ' . $url);
    die();
}

session_start();

if (!isset($_SESSION['PaymentId'])) {
    redirect(FAILURE_URL);
}

$paymentId = $_SESSION['PaymentId'];
try {
    $heidelpay = new Heidelpay('s-priv-2a10BF2Cq2YvAo6ALSGHc3X7F42oWAIp');
    $payment   = $heidelpay->fetchPayment($paymentId);
    if ($payment->isPending()) {
        redirect(SUCCESS_URL);
    }
} catch (HeidelpayApiException $e) {
    redirect(FAILURE_URL);
}
redirect(FAILURE_URL);
