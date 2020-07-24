<?php
/**
 * This service provides for functionalities concerning the mgw environment.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Services
 */
namespace heidelpayPHP\Services;

use function in_array;
use function is_bool;

class EnvironmentService
{
    private const ENV_VAR_NAME_ENVIRONMENT = 'HEIDELPAY_MGW_ENV';
    public const ENV_VAR_VALUE_STAGING_ENVIRONMENT = 'STG';
    public const ENV_VAR_VALUE_DEVELOPMENT_ENVIRONMENT = 'DEV';
    public const ENV_VAR_VALUE_PROD_ENVIRONMENT = 'PROD';

    /** @deprecated ENV_VAR_NAME_DISABLE_TEST_LOGGING since 1.2.7.3 replaced by ENV_VAR_NAME_VERBOSE_TEST_LOGGING */
    public const ENV_VAR_NAME_DISABLE_TEST_LOGGING = 'HEIDELPAY_MGW_DISABLE_TEST_LOGGING';
    public const ENV_VAR_NAME_VERBOSE_TEST_LOGGING = 'HEIDELPAY_MGW_VERBOSE_TEST_LOGGING';

    public const ENV_VAR_TEST_PRIVATE_KEY = 'HEIDELPAY_MGW_TEST_PRIVATE_KEY';
    public const ENV_VAR_TEST_PUBLIC_KEY = 'HEIDELPAY_MGW_TEST_PUBLIC_KEY';
    public const ENV_VAR_TEST_PRIVATE_KEY_NON_3DS = 'HEIDELPAY_MGW_TEST_PRIVATE_KEY_NON_3DS';
    public const ENV_VAR_TEST_PUBLIC_KEY_NON_3DS = 'HEIDELPAY_MGW_TEST_PUBLIC_KEY_NON_3DS';

    private const ENV_VAR_NAME_TIMEOUT = 'HEIDELPAY_MGW_TIMEOUT';
    private const DEFAULT_TIMEOUT = 60;

    private const ENV_VAR_NAME_CURL_VERBOSE = 'HEIDELPAY_MGW_CURL_VERBOSE';

    /**
     * Returns the value of the given env var as bool.
     *
     * @param string $varName
     *
     * @return bool
     */
    protected static function getBoolEnvValue(string $varName): bool
    {
        /** @noinspection ProperNullCoalescingOperatorUsageInspection */
        $envVar = $_SERVER[$varName] ?? false;
        if (!is_bool($envVar)) {
            $envVar = in_array(strtolower(stripslashes($envVar)), [true, 'true', '1'], true);
        }
        return $envVar;
    }

    /**
     * Returns the MGW environment set via environment variable or PROD es default.
     *
     * @return string
     */
    public function getMgwEnvironment(): string
    {
        return stripslashes($_SERVER[self::ENV_VAR_NAME_ENVIRONMENT] ?? self::ENV_VAR_VALUE_PROD_ENVIRONMENT);
    }

    /**
     * Returns false if the logging in tests is deactivated by environment variable.
     *
     * @return bool
     */
    public static function isTestLoggingActive(): bool
    {
        if (isset($_SERVER[self::ENV_VAR_NAME_VERBOSE_TEST_LOGGING])) {
            return self::getBoolEnvValue(self::ENV_VAR_NAME_VERBOSE_TEST_LOGGING);
        }
        return !self::getBoolEnvValue(self::ENV_VAR_NAME_DISABLE_TEST_LOGGING);
    }

    /**
     * Returns the timeout set via environment variable or the default timeout.
     * ATTENTION: Setting this value to 0 will disable the limit.
     *
     * @return int
     */
    public static function getTimeout(): int
    {
        $timeout = stripslashes($_SERVER[self::ENV_VAR_NAME_TIMEOUT] ?? '');
        return is_numeric($timeout) ? (int)$timeout : self::DEFAULT_TIMEOUT;
    }

    /**
     * Returns the curl verbose flag.
     *
     * @return bool
     */
    public static function isCurlVerbose(): bool
    {
        $curlVerbose = strtolower(stripslashes($_SERVER[self::ENV_VAR_NAME_CURL_VERBOSE] ?? 'false'));
        return in_array($curlVerbose, ['true', '1'], true);
    }

    /**
     * Returns the private key string set via environment variable.
     * Returns the non 3ds version of the key if the non3ds flag is set.
     * Returns an empty string if the environment variable is not set.
     *
     * @param bool $non3ds
     *
     * @return string
     */
    public static function getTestPrivateKey($non3ds = false): string
    {
        $variableName = $non3ds ? self::ENV_VAR_TEST_PRIVATE_KEY_NON_3DS : self::ENV_VAR_TEST_PRIVATE_KEY;
        $key = stripslashes($_SERVER[$variableName] ?? '');
        return empty($key) ? '' : $key;
    }

    /**
     * Returns the public key string set via environment variable.
     * Returns the non 3ds version of the key if the non3ds flag is set.
     * Returns an empty string if the environment variable is not set.
     *
     * @param bool $non3ds
     *
     * @return string
     */
    public static function getTestPublicKey($non3ds = false): string
    {
        $variableName = $non3ds ? self::ENV_VAR_TEST_PUBLIC_KEY_NON_3DS : self::ENV_VAR_TEST_PUBLIC_KEY;
        $key = stripslashes($_SERVER[$variableName] ?? '');
        return empty($key) ? '' : $key;
    }
}
