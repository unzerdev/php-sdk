<?php
/**
 * This file contains definitions of the possible operational modes of this SDK.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
 * @package  heidelpay/mgw_sdk/constants
 */
namespace heidelpay\MgwPhpSdk\Constants;

class Mode
{
    /**
     * Test mode: All communication will take place with the heidelpay test servers.
     * @const TEST sandbox mode
     */
    const TEST = 'sandbox';

    /**
     * Live mode: All communication will take place with the heidelpay live servers.
     * @const Live production mode
     */
    const LIVE = 'live';
}
