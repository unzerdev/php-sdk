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
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

class EnvironmentService
{
    const ENV_VAR_NAME_ENVIRONMENT = 'HEIDELPAY_MGW_ENV';
    const ENV_VAR_VALUE_STAGING_ENVIRONMENT = 'STG';
    const ENV_VAR_VALUE_DEVELOPMENT_ENVIRONMENT = 'DEV';
    const ENV_VAR_VALUE_PROD_ENVIRONMENT = 'PROD';

    const ENV_VAR_NAME_DISABLE_TEST_LOGGING = 'HEIDELPAY_MGW_DISABLE_TEST_LOGGING';

    const ENV_VAR_TEST_PRIVATE_KEY = 'HEIDELPAY_MGW_TEST_PRIVATE_KEY';
    const ENV_VAR_TEST_PUBLIC_KEY = 'HEIDELPAY_MGW_TEST_PUBLIC_KEY';
    const DEFAULT_TEST_PRIVATE_KEY = 's-priv-2a102ZMq3gV4I3zJ888J7RR6u75oqK3n';
    const DEFAULT_TEST_PUBLIC_KEY  = 's-pub-2a10ifVINFAjpQJ9qW8jBe5OJPBx6Gxa';

    const ENV_VAR_NAME_TIMEOUT = 'HEIDELPAY_MGW_TIMEOUT';
    const ENV_VAR_DEFAULT_TIMEOUT = 60;

    /**
     * Returns the MGW environment set via environment variable or PROD es default.
     *
     * @return string
     */
    public function getMgwEnvironment(): string
    {
        return $_SERVER[self::ENV_VAR_NAME_ENVIRONMENT] ?? self::ENV_VAR_VALUE_PROD_ENVIRONMENT;
    }

    /**
     * Returns false if the logging in tests is deactivated by environment variable.
     *
     * @return bool
     */
    public static function isTestLoggingActive(): bool
    {
        $testLoggingDisabled = strtolower($_SERVER[self::ENV_VAR_NAME_DISABLE_TEST_LOGGING] ?? 'false');
        return in_array($testLoggingDisabled, ['false', '0'], true);
    }

    /**
     * Returns the timeout set via environment variable or the default timeout.
     * ATTENTION: Setting this value to 0 will disable the limit.
     *
     * @return int
     */
    public static function getTimeout(): int
    {
        $timeout = $_SERVER[self::ENV_VAR_NAME_TIMEOUT] ?? '';
        return is_numeric($timeout) ? (int)$timeout : self::ENV_VAR_DEFAULT_TIMEOUT;
    }

    /**
     * @return string|null
     */
    public function getTestPrivateKey()
    {
        $key = $_SERVER[self::ENV_VAR_TEST_PRIVATE_KEY] ?? '';
        return empty($key) ? self::DEFAULT_TEST_PRIVATE_KEY : $key;
    }

    /**
     * @return string|null
     */
    public function getTestPublicKey()
    {
        $key = $_SERVER[self::ENV_VAR_TEST_PUBLIC_KEY] ?? '';
        return empty($key) ? self::DEFAULT_TEST_PUBLIC_KEY : $key;
    }
}
