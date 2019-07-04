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
 * @link  http://dev.heidelpay.com/
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

    public function getMgwEnvironment()
    {
        return getenv(self::ENV_VAR_NAME_ENVIRONMENT);
    }
}
