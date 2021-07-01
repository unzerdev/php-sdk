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

use UnzerSDK\Adapter\ApplepayAdapter;
use UnzerSDK\Resources\ExternalResources\ApplepaySession;
use UnzerSDK\examples\ExampleDebugHandler;
use UnzerSDK\Exceptions\UnzerApiException;
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

// Catch API errors, write the message to your log and show the ClientMessage to the client.
try {
    // Create an Unzer object using your private key and register a debug handler if you want to.
    $unzer = new Unzer(UNZER_PAPI_PRIVATE_KEY);
    $unzer->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    $unzer->getDebugHandler()->log('--------------- MERCHANT VALIDATION CONTROLLER ----------------');
    $unzer->getDebugHandler()->log(print_r($_POST, 1));

    $domainName = $_SERVER['HTTP_HOST'];

    $applepaySession = new ApplepaySession('merchant.io.unzer.merchantconnectivity', 'PHP-SDK Example', $domainName);
    $unzer->getDebugHandler()->log('session data: ' . print_r($applepaySession->jsonSerialize(), 1));

    $MerchantCertPath = UNZER_EXAMPLE_APPLEPAY_MERCHANT_CERT;
    $MerchantCertKey = UNZER_EXAMPLE_APPLEPAY_MERCHANT_CERT_KEY;
    $merchantCertPathEscaped = realpath($MerchantCertPath);
    $unzer->getDebugHandler()->log('Escaped Path: ' . $merchantCertPathEscaped);

    $appleAdapter = new ApplepayAdapter();
    $appleAdapter->init($MerchantCertPath, $MerchantCertKey);
    $merchantValidationURL = urldecode($_POST['merchantValidationUrl']);
//    $merchantValidationURL = urldecode($_POST['validationURL']);
    $merchantValidationURL = 'https://apple-pay-gateway-cert.apple.com/paymentservices/startSession';
    $validationResponse = $appleAdapter->validateApplePayMerchant(
        $merchantValidationURL,
        $applepaySession
    );
    $responseObject = new stdClass();
    $responseObject->response = json_decode($validationResponse);
    $properties = get_object_vars($responseObject);
    $response = json_encode($properties, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_FORCE_OBJECT);
    $unzer->getDebugHandler()->log('validation response: ' . $validationResponse);
    // Return the validation response to your frontend.
    print_r($validationResponse);

} catch (UnzerApiException $e) {
    $merchantMessage = $e->getMerchantMessage();
    $clientMessage = $e->getClientMessage();
} catch (RuntimeException $e) {
    $merchantMessage = $e->getMessage();
}
