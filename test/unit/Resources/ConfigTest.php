<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Config resource.
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
 * @link     https://docs.unzer.com/
 *
 * @package  UnzerSDK\test\unit
 */

namespace UnzerSDK\test\unit\Resources;

use UnzerSDK\Resources\Config;
use UnzerSDK\test\BasePaymentTest;

class ConfigTest extends BasePaymentTest
{
    /**
     * Verify the constructor of the webhook resource behaves as expected.
     *
     * @test
     */
    public function getterAndSetterWorkAsExpected(): void
    {
        $config = new Config();
        $this->assertNull($config->getDataPrivacyConsent());
        $this->assertNull($config->getDataPrivacyDeclaration());
        $this->assertNull($config->getTermsAndConditions());
        $this->assertIsEmptyArray($config->getQueryParams());

        $config->setDataPrivacyConsent('dataPrivacyConsent')
            ->setDataPrivacyDeclaration('dataPrivacyDeclaration')
            ->setTermsAndConditions('termsAndConditions');

        $this->assertEquals('dataPrivacyConsent', $config->getDataPrivacyConsent());
        $this->assertEquals('dataPrivacyDeclaration', $config->getDataPrivacyDeclaration());
        $this->assertEquals('termsAndConditions', $config->getTermsAndConditions());
    }

    /**
     * Verify query params can be set via Config class.
     *
     * @test
     */
    public function queryParameterCanBeSetViaConfigClass(): void
    {
        $config = new Config();
        $queryParams = $config->getQueryParams();
        $this->assertIsEmptyArray($queryParams);

        $queryArray = [
            'param1' => 'param1',
            'param2' => 'param2',
            'param3' => 'param3',
        ];

        $config->setQueryParams($queryArray);
        $updatedParams = $config->getQueryParams();
        $this->assertEquals('param1', $updatedParams['param1']);
        $this->assertEquals('param2', $updatedParams['param2']);
        $this->assertEquals('param3', $updatedParams['param3']);
    }
}
