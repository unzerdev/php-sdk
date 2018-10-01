<?php
/**
 * Description
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

namespace heidelpay\NmgPhpSdk\Constants;

class ApiResponseCodes
{
    const API_SUCCESS_REQUEST_PROCESSED_IN_TEST_MODE        = 'API.000.100.112';
    const API_SUCCESS_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED   = 'API.100.550.340';
    const API_SUCCESS_CHARGED_AMOUNT_LOWER_THAN_EXPECTED    = 'API.100.550.341';

    const API_ERROR_GENERAL                                 = 'API.000.000.999';
    const API_ERROR_RESOURCE_DOES_NOT_BELONG_TO_MERCHANT    = 'API.320.200.145';
    const API_ERROR_CHARGE_ALREADY_CANCELED                 = 'API.340.100.014';
    const API_ERROR_ALREADY_CHARGED                         = 'API.340.100.018';
    const API_ERROR_INSUFFICIENT_PERMISSIONS                = 'API.710.000.005';
    const API_ERROR_WRONG_AUTHENTICATION_METHOD             = 'API.710.000.007';
    const API_ERROR_FIELD_IS_MISSING                        = 'API.710.200.100';
    const API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED     = 'API.330.100.007';

    const CORE_ERROR_INVALID_OR_MISSING_LOGIN               = 'COR.100.300.600';
}
