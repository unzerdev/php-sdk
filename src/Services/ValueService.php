<?php
/**
 * This service provides for functionalities concerning values and their manipulation.
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
 * @package  UnzerSDK\Services
 */
namespace UnzerSDK\Services;

use function is_float;
use function strlen;

class ValueService
{
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function limitFloats($value)
    {
        if (is_float($value)) {
            $value = round($value, 4);
        }
        return $value;
    }

    /**
     * Mask a value.
     *
     * @param $value
     * @param string $maskSymbol
     *
     * @return string
     */
    public static function maskValue($value, string $maskSymbol = '*'): string
    {
        return substr($value, 0, 6) . str_repeat($maskSymbol, strlen($value) - 10) . substr($value, -4);
    }
}
