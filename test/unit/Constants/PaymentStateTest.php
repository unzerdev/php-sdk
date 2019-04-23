<?php
/**
 * This class defines unit tests to verify functionality of the resource name service.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Constants;

use heidelpayPHP\Constants\PaymentState;
use heidelpayPHP\test\BaseUnitTest;
use RuntimeException;

class PaymentStateTest extends BaseUnitTest
{
    /**
     * This should verify the mapping of the payment state to the state code.
     *
     * @test
     * @dataProvider codeToNameDataProvider
     *
     * @param integer $code
     * @param string  $name
     *
     * @throws RuntimeException
     */
    public function shouldMapCodeToName($code, $name)
    {
        $this->assertEquals($name, PaymentState::mapStateCodeToName($code));
    }

    /**
     * This should verify the mapping of the payment state to the state code.
     *
     * @test
     * @dataProvider nameToCodeDataProvider
     *
     * @param integer $code
     * @param string  $name
     *
     * @throws RuntimeException
     */
    public function shouldMapNameToCode($name, $code)
    {
        $this->assertEquals($code, PaymentState::mapStateNameToCode($name));
    }

    /**
     * This verifies that an exception is thrown when the code to map is unknown.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function mapCodeToNameShouldThrowAnExceptionIfTheCodeIsUnknown()
    {
        $this->expectException(RuntimeException::class);

        PaymentState::mapStateCodeToName(6);
    }

    /**
     * This verifies that an exception is thrown when the name to map is unknown.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function mapNameToCodeShouldThrowAnExceptionIfTheNameIsUnknown()
    {
        $this->expectException(RuntimeException::class);

        PaymentState::mapStateNameToCode('unknown');
    }

    //<editor-fold desc="Data Providers">

    /**
     * Provides data to test code to name mapper.
     *
     * @return array
     */
    public function codeToNameDataProvider(): array
    {
        return[
            [PaymentState::STATE_PENDING, 'pending'],
            [PaymentState::STATE_COMPLETED, 'completed'],
            [PaymentState::STATE_CANCELED, 'canceled'],
            [PaymentState::STATE_PARTLY, 'partly'],
            [PaymentState::STATE_PAYMENT_REVIEW, 'payment review'],
            [PaymentState::STATE_CHARGEBACK, 'chargeback']
        ];
    }

    /**
     * Provides data to test name to code mapper.
     *
     * @return array
     */
    public function nameToCodeDataProvider(): array
    {
        return[
            [PaymentState::STATE_NAME_PENDING, 0],
            [PaymentState::STATE_NAME_COMPLETED, 1],
            [PaymentState::STATE_NAME_CANCELED, 2],
            [PaymentState::STATE_NAME_PARTLY, 3],
            [PaymentState::STATE_NAME_PAYMENT_REVIEW, 4],
            [PaymentState::STATE_NAME_CHARGEBACK, 5]
        ];
    }

    //</editor-fold>
}
