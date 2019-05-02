<?php
/**
 * This service provides for all methods concerning id strings.
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

use function count;
use RuntimeException;

class IdService
{
    /**
     * Returns the id for the given resource type from the given Rest-URL string.
     * Resource type is given as idString as defined in IdStrings constants.
     * Only takes the id into account if it is at the end of the url string.
     * Throws exception if the id can not be detected.
     *
     * @param string $url
     * @param string $idString
     * @param bool   $onlyLast
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public static function getResourceIdFromUrl($url, $idString, $onlyLast = false): string
    {
        $matches = [];
        $pattern = '/\/([s|p]{1}-' . $idString . '-[a-z\d]+)\/?' . ($onlyLast ? '$':'') . '/';
        preg_match($pattern, $url, $matches);

        if (count($matches) < 2) {
            throw new RuntimeException('Id for "' . $idString . '" not found in "' . $url . '"!');
        }

        return $matches[1];
    }

    /**
     * Behaves like getResourceIdFromUrl but does not throw exception but returns null if the id can not be detected.
     *
     * @param string $url
     * @param string $idString
     * @param bool   $onlyLast
     *
     * @return string|null
     */
    public static function getResourceIdOrNullFromUrl($url, $idString, $onlyLast = false)
    {
        try {
            return self::getResourceIdFromUrl($url, $idString, $onlyLast);
        } catch (RuntimeException $e) {
            return null;
        }
    }

    /**
     * @param string $url
     *
     * @return string|null
     */
    public static function getLastResourceIdFromUrlString($url)
    {
        return self::getResourceIdOrNullFromUrl($url, '([a-z]{3}|p24)', true);
    }

    /**
     * @param $typeId
     *
     * @return string|null
     */
    public static function getResourceTypeFromIdString($typeId)
    {
        $paymentType  = null;
        $typeIdString = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3}|p24)-\d*/', $typeId, $typeIdParts);

        if (count($typeIdParts) >= 2) {
            $typeIdString = $typeIdParts[1];
        }

        return $typeIdString;
    }
}
