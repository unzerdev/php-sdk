<?php
/**
 * This interface defines the methods for a parent heidelpay object.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Interfaces
 */
namespace heidelpayPHP\Interfaces;

use heidelpayPHP\Heidelpay;
use RuntimeException;

interface HeidelpayParentInterface
{
    /**
     * Returns the heidelpay root object.
     *
     * @return Heidelpay
     *
     * @throws RuntimeException
     */
    public function getHeidelpayObject(): Heidelpay;

    /**
     * Returns the url string for this resource.
     *
     * @param bool $appendId
     *
     * @return string
     */
    public function getUri($appendId = true): string;
}
