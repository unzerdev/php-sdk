<?php
/**
 * This provides validation functions concerning expiry dates.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @package  heidelpayPHP/validators
 */
namespace heidelpayPHP\Validators;

class ExpiryDateValidator
{
    /**
     * Returns true if the given expiry date has a valid format.
     *
     * @param string $expiryDate
     *
     * @return bool
     */
    public static function validate($expiryDate): bool
    {
        return preg_match('/^(0[\d]|1[0-2]|[1-9]){1}\/(\d{2}|\d{4})$/', $expiryDate);
    }
}
