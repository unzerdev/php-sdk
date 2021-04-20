<?php
/**
 * For security reasons all examples are disabled by default
 * You can switch the constant 'UNZER_PAPI_EXAMPLES' to true to make the examples executable.
 * But you should always set it false on productive environments.
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

/* Set to true if you want to enable the examples */
define('UNZER_PAPI_EXAMPLES', false);

/* Please set this to your url. It must be reachable over the net
Webhooks will work with https only. However protocol can be changed to http if necessary. */
define('UNZER_PAPI_URL', 'https://'.$_SERVER['HTTP_HOST']);

/* Please enter the path from root directory to the example folder */
define('UNZER_PAPI_FOLDER', '/vendor/unzerdev/php-sdk/examples/');

/* Please provide your own sandbox-keypair here. */
define('UNZER_PAPI_PRIVATE_KEY', 's-priv-***');
define('UNZER_PAPI_PUBLIC_KEY', 's-pub-***');
