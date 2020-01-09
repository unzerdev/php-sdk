<?php
/**
 * This file provides an example implementation of the Hire Purchase direct debit payment type.
 * It shows the selected payment plan to the customer who can approve the plan to perform the payment.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */
/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';

session_start();

$externalOrderId = $_SESSION['externalOrderId'] ?? 'no external order id provided';
$zgReferenceId = $_SESSION['zgReferenceId'] ?? 'no reference id provided';
$PDFLink = $_SESSION['PDFLink'] ?? 'no link provided';

?>

<!DOCTYPE html>
<html lang="en">
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
        <i class="search icon"></i>
        <span class="content">
            Confirm instalment plan
            <span class="sub header">Download the instalment plant information and confirm your order...</span>
        </span>
    </h2>
</div>

<div class="ui container">
    <div class="ui attached segment">
        <strong>Please download your rate plan <a href="<?php echo (string)($PDFLink); ?>">here</a></strong><br/>
    </div>
    <div class="ui bottom attached primary button" tabindex="0" onclick="location.href='PlaceOrderController.php'">Place order</div>
</div>

</body>
</html>
