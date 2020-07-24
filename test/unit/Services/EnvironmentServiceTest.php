<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This test is verifying that the set environment variables will lead to the correct configuration.
 *
 * Copyright (C) 2020 heidelpay GmbH
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
 * @package  heidelpayPHP\test\unit\Services
 */
namespace heidelpayPHP\test\unit\Services;

use heidelpayPHP\Services\EnvironmentService;
use PHPUnit\Framework\TestCase;

class EnvironmentServiceTest extends TestCase
{
    //<editor-fold desc="Tests">

    /**
     * Verify test logging environment vars are correctly interpreted.
     *
     * @test
     * @dataProvider envVarsShouldBeInterpretedAsExpectedDP
     *
     * @param mixed $logDisabled
     * @param mixed $verboseLog
     * @param bool  $expectedLogEnabled
     */
    public function envVarsShouldBeInterpretedAsExpected($logDisabled, $verboseLog, $expectedLogEnabled): void
    {
        unset(
            $_SERVER[EnvironmentService::ENV_VAR_NAME_DISABLE_TEST_LOGGING],
            $_SERVER[EnvironmentService::ENV_VAR_NAME_VERBOSE_TEST_LOGGING]
        );

        if ($logDisabled !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_NAME_DISABLE_TEST_LOGGING] = $logDisabled;
        }

        if ($verboseLog !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_NAME_VERBOSE_TEST_LOGGING] = $verboseLog;
        }

        $this->assertEquals($expectedLogEnabled, EnvironmentService::isTestLoggingActive());
    }

    /**
     * Verify string is returned if the private test key environment variable is not set.
     *
     * @test
     *
     * @dataProvider keyStringIsReturnedCorrectlyDP
     *
     * @param string  $keyEnvVar
     * @param string  $non3dsKeyEnvVar
     * @param boolean $non3ds
     * @param string  $expected
     */
    public function privateKeyStringIsReturnedCorrectly($keyEnvVar, $non3dsKeyEnvVar, $non3ds, $expected): void
    {
        unset(
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PRIVATE_KEY],
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PRIVATE_KEY_NON_3DS]
        );

        if ($keyEnvVar !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PRIVATE_KEY] = $keyEnvVar;
        }

        if ($non3dsKeyEnvVar !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PRIVATE_KEY_NON_3DS] = $non3dsKeyEnvVar;
        }

        $this->assertEquals($expected, EnvironmentService::getTestPrivateKey($non3ds));
    }

    /**
     * Verify string is returned if the public test key environment variable is not set.
     *
     * @test
     *
     * @dataProvider keyStringIsReturnedCorrectlyDP
     *
     * @param string  $keyEnvVar
     * @param string  $non3dsKeyEnvVar
     * @param boolean $non3ds
     * @param string  $expected
     */
    public function publicKeyStringIsReturnedCorrectly($keyEnvVar, $non3dsKeyEnvVar, $non3ds, $expected): void
    {
        unset(
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PUBLIC_KEY],
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PUBLIC_KEY_NON_3DS]
        );

        if ($keyEnvVar !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PUBLIC_KEY] = $keyEnvVar;
        }

        if ($non3dsKeyEnvVar !== null) {
            $_SERVER[EnvironmentService::ENV_VAR_TEST_PUBLIC_KEY_NON_3DS] = $non3dsKeyEnvVar;
        }

        $this->assertEquals($expected, EnvironmentService::getTestPublicKey($non3ds));
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    /**
     * Data provider for envVarsShouldBeInterpretedAsExpected.
     *
     * @return array
     */
    public function envVarsShouldBeInterpretedAsExpectedDP(): array
    {
        return [
            '#0' => [null, null, true],
            '#1' => [0, null, true],
            '#2' => [1, null, false],
            '#3' => [null, 0, false],
            '#4' => [null, 1, true],
            '#5' => [0, 0, false],
            '#6' => [0, 1, true],
            '#7' => [1, 0, false],
            '#8' => [1, 1, true],
            '#9' => ["false", null, true],
            '#10' => ["true", null, false],
            '#11' => [null, "false", false],
            '#12' => [null, "true", true],
            '#13' => ["false", "false", false],
            '#14' => ["false", "true", true],
            '#15' => ["true", "false", false],
            '#16' => ["true", "true", true],
            '#17' => [false, null, true],
            '#18' => [true, null, false],
            '#19' => [null, false, false],
            '#20' => [null, true, true],
            '#21' => [false, false, false],
            '#22' => [false, true, true],
            '#23' => [true, false, false],
            '#24' => [true, true, true],
            '#25' => ['fals', null, true],
            '#26' => ['tru', null, true],
            '#27' => [null, 'fals', false],
            '#28' => [null, 'tru', false],
            '#29' => ['fals', 'fals', false],
            '#30' => ['fals', 'tru', false],
            '#31' => ['tru', 'fals', false],
            '#32' => ['tru', 'tru', false],
            '#33' => ['false', 'fals', false],
            '#34' => ['false', 'tru', false],
            '#35' => ['true', 'fals', false],
            '#36' => ['true', 'tru', false],
            '#37' => ['fals', 'false', false],
            '#38' => ['fals', 'true', true],
            '#39' => ['tru', 'false', false],
            '#40' => ['tru', 'true', true],
        ];
    }

    /**
     * Data provider for privateKeyStringIsReturnedCorrectly and publicKeyStringIsReturnedCorrectly.
     *
     * @return array
     */
    public function keyStringIsReturnedCorrectlyDP(): array
    {
        return [
            'expect empty string for 3ds' => [null, null, false, ''],
            'expect empty string for non 3ds' => [null, null, true, ''],
            'expect string from 3ds Env Var' => ['I am the 3ds key', 'I am the non 3ds key', false, 'I am the 3ds key'],
            'expect string from non 3ds Env Var' => ['I am the 3ds key', 'I am the non 3ds key', true, 'I am the non 3ds key']
        ];
    }

    //</editor-fold>
}
