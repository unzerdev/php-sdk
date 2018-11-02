<?php
/**
 * This is the php partial for the example controllers.
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

if (!isset($_POST['paymentTypeId'])) {
    redirect(FAILURE_URL);
}
$paymentTypeId   = $_POST['paymentTypeId'];

session_start();

function redirect($url) {
    $response[] = ['result' => 'redirect', 'redirectUrl' => $url];
    header('Content-Type: application/json');
    echo json_encode($response);
    die;
}

function returnError($message) {
    header('HTTP/1.1 500 Internal Server Error');
    addMessage('error', $message);
    returnResponse();
}

function addSuccess($message) {
    addMessage('success', $message);
}

function addInfo($message) {
    addMessage('info', $message);
}

function addMessage($type, $message) {
    $GLOBALS['response'][] = ['result' => $type, 'message' => $message];
}

function returnResponse() {
    header('Content-Type: application/json');
    echo json_encode($GLOBALS['response']);
    die;
}