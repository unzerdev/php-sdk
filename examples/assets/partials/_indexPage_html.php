<?php
/**
 * This is the html partial for the payment example index pages.
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
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>
        Heidelpay UI Examples
    </title>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.min.css" />

    <link rel="stylesheet" href="https://static.heidelpay.com/v1/heidelpay.css" />
    <script type="text/javascript" src="https://static.heidelpay.com/v1/heidelpay.js"></script>

    <script type="text/javascript" src="<?php echo EXAMPLE_BASE_FOLDER . 'assets/js/logger.js' ?>"></script>
    <script type="text/javascript" src="<?php echo EXAMPLE_BASE_FOLDER . 'assets/js/dimmer.js' ?>"></script>

    <style>
        html, body {
            margin: 0;
            padding: 70px 0 0;
            height: 330px;
            min-width: initial;
        }
    </style>
</head>
