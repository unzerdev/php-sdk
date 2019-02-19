<?php
/**
 * This provides validation functions concerning secret keys.
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

class KeyValidator
{
    /**
     * Returns true if the given private key has a valid format.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function validatePrivateKey($key): bool
    {
        return self::validate($key);
    }

    /**
     * Returns true if the given public key has a valid format.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function validatePublicKey($key): bool
    {
        return self::validate($key, false);
    }

    /**
     * Returns true if the given key is valid.
     * If the flag $privateKey is set it will be checked to be private if not it is checked to be a valid public key.
     *
     * @param $key
     * @param bool $privateKey
     * @return bool
     */
    public static function validate($key, $privateKey = true): bool
    {
        $match = [];
        $keyType = $privateKey ? 'priv' : 'pub';
        preg_match('/^[sp]{1}-(priv|pub)-[a-zA-Z0-9]+/', $key, $match);
        return !(\count($match) < 2 || $match[1] !== $keyType);
    }
}
