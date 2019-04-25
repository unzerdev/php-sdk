<?php
/**
 * This file provides a list of the example implementations.
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
require_once __DIR__ . '/../../../autoload.php';
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

        <link rel="stylesheet" href="https://static.heidelpay.com/v1/heidelpay.css" />
        <script type="text/javascript" src="https://static.heidelpay.com/v1/heidelpay.js"></script>
    </head>

    <body style="margin: 70px 70px 0;">
        <div class="ui container segment">
            <h2 class="ui header">
                <i class="shopping cart icon"></i>
                <div class="content">
                    Payment Implentation Examples
                    <div class="sub header">Choose the Payment Type you want to evaluate...</div>
                </div>
            </h2>
            <div class="ui cards">
                <div class="card">
                    <div class="content">
                        <div class="header"><i class="credit card icon"></i> Credit Card</div>
                        <div class="description">
                            You can try authorize and charge transaction with or without 3Ds
                        </div>
                    </div>
                    <div class="ui bottom attached button" onclick="location.href='CreditCard/';">
                        Try
                    </div>
                </div>
                <div class="card">
                    <div class="content">
                        <div class="header">
                            EPS
                        </div>
                        <div class="description">
                            Charge
                        </div>
                    </div>
                    <div class="ui bottom attached button" onclick="location.href='EPSCharge/';">
                        Try
                    </div>
                </div>
                <div class="card">
                    <div class="content">
                        <div class="header">
                            Alipay
                        </div>
                        <div class="description">
                            Charge
                        </div>
                    </div>
                    <div class="ui bottom attached button" onclick="location.href='Alipay/';">
                        Try
                    </div>
                </div>
                <div class="card">
                    <div class="content">
                        <div class="header">
                            Register Webhooks
                        </div>
                        <div class="description">
                            Enable a log output in ExampleDebugHandler to see the events coming in.
                        </div>
                    </div>
                    <div class="ui bottom attached button" onclick="location.href='Webhooks/';">
                        Try
                    </div>
                </div>
            </div>
        </div>
    </body>

</html>
