<?php
/**
 * This class defines integration tests to verify general functionalities.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\SupportedLocale;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class GeneralTest extends BasePaymentTest
{
    /**
     * Validate valid keys are accepted.
     *
     * @test
     * @dataProvider validKeysDataProvider
     *
     * @param string $key
     *
     * @throws HeidelpaySdkException
     * @throws ExpectationFailedException
     * @throws Exception
     */
    public function validKeysShouldBeExcepted($key)
    {
        $heidelpay = new Heidelpay($key, SupportedLocale::GERMAN_GERMAN);
        $this->assertEquals($key, $heidelpay->getKey());
    }

    /**
     * Validate invalid keys are revoked.
     *
     * @test
     * @dataProvider invalidKeysDataProvider
     *
     * @param string $key
     *
     * @throws HeidelpaySdkException
     * @throws Exception
     */
    public function invalidKeysShouldResultInException($key)
    {
        $this->expectException(HeidelpaySdkException::class);
        new Heidelpay($key, SupportedLocale::GERMAN_GERMAN);
    }
}
