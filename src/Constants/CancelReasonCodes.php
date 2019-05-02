<?php
/**
 * This file contains the different cancel reason codes.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/constants
 */
namespace heidelpayPHP\Constants;

class CancelReasonCodes
{
    const REASON_CODE_CANCEL = 'CANCEL';
    const REASON_CODE_RETURN = 'RETURN';
    const REASON_CODE_CREDIT = 'CREDIT';

    const REASON_CODE_ARRAY = [
        self::REASON_CODE_CANCEL,
        self::REASON_CODE_RETURN,
        self::REASON_CODE_CREDIT
    ];
}
