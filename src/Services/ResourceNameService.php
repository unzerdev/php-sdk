<?php
/**
 * This service provides for functionalities concerning resource names.
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
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

class ResourceNameService
{
    /**
     * Extracts the short name of the given full qualified class name.
     *
     * @param string $classString
     *
     * @return string
     */
    public static function getClassShortName($classString): string
    {
        $classNameParts = explode('\\', $classString);
        return end($classNameParts);
    }

    /**
     * Return class short name.
     *
     * @param string $classString
     *
     * @return string
     */
    public static function getClassShortNameKebapCase($classString): string
    {
        return self::toKebapCase(self::getClassShortName($classString));
    }

    /**
     * Change camel case string to kebap-case.
     *
     * @param string $str
     *
     * @return string
     */
    private static function toKebapCase($str): string
    {
        $kebapCaseString = preg_replace_callback(
            '/([A-Z][a-z]{1})+/',
            static function ($str) {
                return '-' . strtolower($str[0]);
            },
            lcfirst($str)
        );
        return strtolower($kebapCaseString);
    }
}
