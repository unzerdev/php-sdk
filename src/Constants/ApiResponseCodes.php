<?php
/**
 * This file contains definitions of common response codes.
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

class ApiResponseCodes
{
    const API_SUCCESS_REQUEST_PROCESSED_IN_TEST_MODE        = 'API.000.100.112';
    const API_SUCCESS_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED   = 'API.100.550.340';
    const API_SUCCESS_CHARGED_AMOUNT_LOWER_THAN_EXPECTED    = 'API.100.550.341';

    const API_ERROR_GENERAL                                 = 'API.000.000.999';
    const API_ERROR_RESOURCE_DOES_NOT_BELONG_TO_MERCHANT    = 'API.320.200.145';
    const API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED     = 'API.330.100.007';
    const API_ERROR_CHARGE_ALREADY_CANCELED                 = 'API.340.100.014';
    const API_ERROR_ALREADY_CHARGED                         = 'API.340.100.018';
    const API_ERROR_CUSTOMER_DOES_NOT_EXIST                 = 'API.410.100.100';
    const API_ERROR_INSUFFICIENT_PERMISSIONS                = 'API.710.000.005';
    const API_ERROR_WRONG_AUTHENTICATION_METHOD             = 'API.710.000.007';
    const API_ERROR_FIELD_IS_MISSING                        = 'API.710.200.100';

    const CORE_ERROR_INVALID_OR_MISSING_LOGIN               = 'COR.100.300.600';
}
