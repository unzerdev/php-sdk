<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
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
    const API_ERROR_INSUFFICIENT_PERMISSIONS                = 'API.710.000.005';
    const API_ERROR_WRONG_AUTHENTICATION_METHOD             = 'API.710.000.007';
    const API_ERROR_FIELD_IS_MISSING                        = 'API.710.200.100';

    const CORE_ERROR_INVALID_OR_MISSING_LOGIN               = 'COR.100.300.600';
}
