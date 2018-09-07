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
    const API_SUCCESS_REQUEST_PROCESSED_IN_TEST_MODE = 'API.000.100.112';

    const API_ERROR_CHARGE_ALREADY_CANCELED = 'API.340.100.014';
    const API_ERROR_WRONG_AUTHENTICATION_METHOD = 'API.710.000.007';

    const CORE_ERROR_INVALID_OR_MISSING_LOGIN = 'COR.100.300.600';
}
