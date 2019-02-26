<?php
/**
 * This is the controller for the Webhook reception tests.
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

use heidelpayPHP\examples\ExampleDebugHandler;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;

try {
    //#######  2. Create a heidelpay object using your private key #####################################################
    $heidelpay = new Heidelpay('s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    // todo: Refactor and Remove
    $postData = $_POST;
    $heidelpay->debugLog('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>POST: ' . print_r($postData, 1));

//    $postData = '{ "event":"types", "publicKey":"s-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa", "retrieveUrl":"https://api.heidelpay.com/v1/types/card/s-crd-88xu7qjboupc" }';
//    $heidelpay->fetchResourceByWebhookEvent($postData);

} catch (HeidelpayApiException $e) {
//    redirect(FAILURE_URL);
}
//redirect(FAILURE_URL);
