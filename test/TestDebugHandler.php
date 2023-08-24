<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @package  UnzerSDK\test\integration
 */

namespace UnzerSDK\test;

use UnzerSDK\Interfaces\DebugHandlerInterface;

class TestDebugHandler implements DebugHandlerInterface
{
    /** @var string $tempLog Stores the log messages until reset via clearTempLog() or echoed out via dumpTempLog(). */
    private $tempLog = '';

    /**
     * {@inheritDoc}
     */
    public function log(string $message): void
    {
        $logMessage = 'Unzer debug message: ' . $message . "\n";

        if (Helper\TestEnvironmentService::isTestLoggingActive()) {
            // Echo log messages directly.
            echo $logMessage;
        } else {
            // Store log to echo it when needed.
            $this->tempLog .= $logMessage;
        }
    }

    /**
     * Clears the temp log.
     */
    public function clearTempLog(): void
    {
        $this->tempLog = '';
    }

    /**
     * Echos the contents of tempLog and clears it afterwards.
     */
    public function dumpTempLog(): void
    {
        echo $this->tempLog;
        $this->clearTempLog();
    }
}
