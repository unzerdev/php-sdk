<?php
/**
 * This is the controller for the Card example.
 * It is called when the pay button on the index page is clicked.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\examples
 */
header('Access-Control-Allow-Origin: https://dev-demo.unzer.com/');

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Applepay;
use UnzerSDK\Unzer;

session_start();
session_unset();

$clientMessage = 'Something went wrong. Please try again later.';
$merchantMessage = 'Something went wrong. Please try again later.';

function redirect($url, $merchantMessage = '', $clientMessage = '')
{
    $_SESSION['merchantMessage'] = $merchantMessage;
    $_SESSION['clientMessage']   = $clientMessage;
    header('Location: ' . $url);
    die();
}

//$paymentTypeId   = $_POST['resourceId'];

// These lines are just for this example
$transactionType = $_POST['transaction_type'] ?? 'authorize';
$use3Ds          = isset($_POST['3dsecure']) && ($_POST['3dsecure'] === '1');
$AppleAuthorization = $_POST['applePayAuthorisation'];

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create an Unzer object using your private key and register a debug handler if you want to.
    $unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
    $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());
    $applepay = new Applepay(null, null, null, null);
    $unzer->getDebugHandler()->log("\n ------------------------  AUTHORIZATION JSON-----------------------");
    $applepay->handleResponse(json_decode($AppleAuthorization));
    $unzer->createPaymentType($applepay);
    $unzer->getDebugHandler()->log('Authorization: ' . $AppleAuthorization);
    $unzer->getDebugHandler()->log("\n ------------------------  AUTHORIZATION PAYMENT TYPE-----------------------");
    $unzer->getDebugHandler()->log('Authorization: ' . $AppleAuthorization);

    $transaction = $applepay->charge(100, 'EUR', RETURN_CONTROLLER_URL);
    $unzer->getDebugHandler()->log('Merchant authorized controller');
    if ($transaction->isSuccess()) {
        echo json_encode(['result' => true]);
        return;
    }

} catch (UnzerApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
    echo json_encode(['result' => false]);
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
