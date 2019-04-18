<?php
/**
 * This is the index controller for the Webhook tests.
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

use heidelpayPHP\Constants\WebhookEvents;
use heidelpayPHP\examples\ExampleDebugHandler;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Webhook;

try {
    $heidelpay = new Heidelpay('s-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n');
    $heidelpay->setDebugMode(true)->setDebugHandler(new ExampleDebugHandler());

    $heidelpay->deleteAllWebhooks();

    $webhooks = $heidelpay->registerMultipleWebhooks(CONTROLLER_URL, [WebhookEvents::ALL]);

    echo 'Events registered: <br>';
    foreach ($webhooks as $webhook) {
        /** @var Webhook $webhook */
        echo print_r($webhook->expose(), 1) . '<br>';

    }
} catch (HeidelpayApiException $e) {
    $heidelpay->debugLog('Error: ' . $e->getMessage());
} catch (RuntimeException $e) {
    $heidelpay->debugLog('Error: ' . $e->getMessage());
}
