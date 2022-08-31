<?php
/**
 * This file provides an example implementation of the Invoice payment type.
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
 * @package  UnzerSDK\examples
 */

/** Require the constants of this example */
require_once __DIR__ . '/Constants.php';

/** @noinspection PhpIncludeInspection */

/** Require the composer autoloader file */
require_once __DIR__ . '/../../../../autoload.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unzer UI Examples</title>

    <link rel="stylesheet" href="https://static.unzer.com/v1/unzer.css" />
    <script type="text/javascript" src="https://static.unzer.com/v1/unzer.js"></script>
</head>

<body style="margin: 70px 70px 0;">

<form id="payment-form" action="<?php echo CONTROLLER_URL; ?>" class="unzerUI form" novalidate>
    <div class="field">
        <button class="unzerUI primary button fluid" id="submit-button" type="submit">Pay</button>
    </div>
</form>

<script>
    // Create an Unzer instance with your public key
    // This is not actually needed for this example but we want the sandbox banner to show on the page.
    let unzerInstance = new unzer('<?php echo UNZER_PAPI_PUBLIC_KEY; ?>');
</script

</body>
</html>
