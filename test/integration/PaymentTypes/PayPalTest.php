<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the payment method invoice.
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

use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class PaypalTest extends BasePaymentTest
{
    /**
     * Verify PayPal payment type can be created and fetched.
     *
     * @test
     */
    public function paypalShouldBeCreatableAndFetchable()
    {
		$paypal = $this->heidelpay->createPaymentType(new Paypal());
		$this->assertInstanceOf(Paypal::class, $paypal);
		$this->assertNotEmpty($paypal->getId());

		$fetchedPaypal = $this->heidelpay->fetchPaymentType($paypal->getId());
        $this->assertInstanceOf(Paypal::class, $fetchedPaypal);
        $this->assertNotSame($paypal, $fetchedPaypal);
		$this->assertEquals($paypal->expose(), $fetchedPaypal->expose());

		return $fetchedPaypal;
    }
}
