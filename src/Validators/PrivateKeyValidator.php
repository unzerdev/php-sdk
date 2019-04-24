<?php
/**
 * This provides validation functions concerning private key.
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

use function count;

class PrivateKeyValidator
{
    /**
     * Returns true if the given private key has a valid format.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function validate($key): bool
    {
        $match = [];
        preg_match('/^[sp]{1}-priv-[a-zA-Z0-9]+/', $key, $match);
        return count($match) > 0;
    }
}
