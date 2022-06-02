<?php
/**
 * This custom debug handler will echo out debug messages.
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
 * @package  UnzerSDK\examples
 */
namespace UnzerSDK\examples;

use UnzerSDK\Interfaces\DebugHandlerInterface;

class ExampleDebugHandler implements DebugHandlerInterface
{
    private const LOG_TYPE_APPEND_TO_FILE = 3;

    /**
     * {@inheritDoc}
     *
     * ATTENTION: Please make sure the destination file is writable.
     */
    public function log(string $message): void
    {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($message . "\n", self::LOG_TYPE_APPEND_TO_FILE, __DIR__ . '/log/example.log');
    }
}
