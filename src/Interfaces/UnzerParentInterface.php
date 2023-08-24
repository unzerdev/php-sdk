<?php
/**
 * This interface defines the methods for a parent Unzer object.
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
 * @package  UnzerSDK\Interfaces
 */

namespace UnzerSDK\Interfaces;

use UnzerSDK\Unzer;
use RuntimeException;
use UnzerSDK\Adapter\HttpAdapterInterface;

interface UnzerParentInterface
{
    /**
     * Returns the Unzer root object.
     *
     * @return Unzer
     *
     * @throws RuntimeException
     */
    public function getUnzerObject(): Unzer;

    /**
     * Returns the url string for this resource.
     *
     * @param bool   $appendId
     * @param string $httpMethod
     *
     * @return string
     */
    public function getUri(bool $appendId = true, string $httpMethod = HttpAdapterInterface::REQUEST_GET): string;
}
