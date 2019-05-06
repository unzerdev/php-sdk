<?php
/**
 * This is the index controller for the Webhook tests.
 *
 * Copyright (C) 2019 heidelpay GmbH
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

function printMessage($type, $title, $text)
{
    echo '<div class="ui ' . $type . ' message">'.
            '<div class="header">' . $title . '</div>'.
            '<p>' . nl2br($text) . '</p>'.
         '</div>';
}

function printError($text)
{
    printMessage('error', 'Error', $text);
}

function printSuccess($title, $text)
{
    printMessage('success', $title, $text);
}

function printInfo($title, $text)
{
    printMessage('info', $title, $text);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>
        Heidelpay UI Examples
    </title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"
            integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />
</head>

<body style="margin: 70px 70px 0;">
<div class="ui container segment">
    <h2 class="ui header">
        <i class="envelope outline icon"></i>
        <div class="content">
            Webhook registration
        </div>
    </h2>

    <?php
        // Show info message if the general test keys are used
        if (DEFAULT_PRIVATE_KEY === HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY) {
            printMessage(
                'yellow',
                'Attention: You are using the default key pair!',
                "Keep in mind that webhooks are registered for the private key used to create them.\n" .
                "This may lead to unwanted behaviour, since someone else using the same key pair might change your webhooks e.g. by deleting them.\n" .
                'We suggest you replace the predefined key pair in file _enableExamples.php with your own'
            );
        }

        try {
            $heidelpay = new Heidelpay(HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY);
            $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

            $heidelpay->deleteAllWebhooks();
            printSuccess(
                'De-registered all existing events for this private key',
                'Unsubscribed all events registered for the private key: "' . HEIDELPAY_PHP_PAYMENT_API_PRIVATE_KEY . '".'
            );

        } catch (HeidelpayApiException $e) {
            printError($e->getMessage());
            $heidelpay->debugLog('Error: ' . $e->getMessage());
        } catch (RuntimeException $e) {
            printError($e->getMessage());
            $heidelpay->debugLog('Error: ' . $e->getMessage());
        }

        printInfo('Back to the payment selection', 'Now Perform payments <a href="..">>> HERE <<</a> to trigger events!');
    ?>
</div>
</body>
</html>
