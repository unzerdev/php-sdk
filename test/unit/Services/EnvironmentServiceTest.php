<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This test is verifying that the set environment variables will lead to the correct configuration.
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
 * @package  UnzerSDK\test\unit\Services
 */
namespace UnzerSDK\test\unit\Services;

use UnzerSDK\Services\EnvironmentService;
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
     * @param mixed $verboseLog
     * @param bool  $expectedLogEnabled
     */
    public function envVarsShouldBeInterpretedAsExpected($verboseLog, $expectedLogEnabled): void
    {
        unset(
            $_SERVER[EnvironmentService::ENV_VAR_NAME_VERBOSE_TEST_LOGGING]
        );

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
            '#0' =>     [null, false],
            '#1' =>     [0, false],
            '#2' =>     [1, true],
            '#3' =>     [false, false],
            '#4' =>     [true, true],
            '#5' =>     ["false", false],
            '#6' =>     ["true", true],
            '#7' =>     ['fals', false],
            '#8' =>     ['tru', false],
            '#9' =>     [010, false],
            '#10' =>    ['1', true],
            '#11' =>    ['100', false],
            '#12' =>    ['0', false],
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
