<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the payment method Przelewy24.
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
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class Przelewy24Test extends BasePaymentTest
{
    /**
     * Verify Przelewy24 payment type can be created and fetched.
     *
     * @test
     */
    public function przelewy24ShouldBeCreatableAndFetchable()
    {
        $przelewy24 = $this->heidelpay->createPaymentType(new Przelewy24());
        $this->assertInstanceOf(Przelewy24::class, $przelewy24);
        $this->assertNotEmpty($przelewy24->getId());

        $fetchedPrzelewy24 = $this->heidelpay->fetchPaymentType($przelewy24->getId());
        $this->assertInstanceOf(Przelewy24::class, $fetchedPrzelewy24);
        $this->assertNotSame($przelewy24, $fetchedPrzelewy24);
        $this->assertEquals($przelewy24->expose(), $fetchedPrzelewy24->expose());

        return $fetchedPrzelewy24;
    }
}
