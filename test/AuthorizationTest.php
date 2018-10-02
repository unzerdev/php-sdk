<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the authorization transaction type.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */

namespace heidelpay\MgwPhpSdk\test;

use heidelpay\MgwPhpSdk\Constants\Currency;

class AuthorizationTest extends BasePaymentTest
{
    /**
     * Verify heidelpay object can perform an authorization based on the paymentTypeId.
     *
     * @test
     */
    public function authorizeWithTypeId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorize = $this->heidelpay->authorizeWithPaymentTypeId(
            100.0,
            Currency::EUROPEAN_EURO,
            $card->getId(),
            self::RETURN_URL
        );
        $this->assertNotNull($authorize);
        $this->assertNotNull($authorize->getId());
    }


    /**
     * Verify heidelpay object can perform an authorization based on the paymentType object.
     *
     * @test
     */
    public function authorizeWithType()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorize = $this->heidelpay->authorize(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL);
        $this->assertNotNull($authorize);
        $this->assertNotNull($authorize->getId());
    }

    /**
     * Verify authorization produces Payment and Customer.
     *
     * @test
     */
    public function authorizationProducesPaymentAndCustomer()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $customer = $this->getMinimalCustomer();
        $this->assertNull($customer->getId());

        $authorize = $this->heidelpay->authorize(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL, $customer);
        $payment = $authorize->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        $newCustomer = $payment->getCustomer();
        $this->assertNotNull($newCustomer);
        $this->assertNotNull($newCustomer->getId());
    }

    /**
     * Verify authorization with customer Id.
     *
     * @test
     */
    public function authorizationWithCustomerId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $customerId = $this->heidelpay->createCustomer($this->getMinimalCustomer())->getId();
        $authorize = $this->heidelpay->authorize(100.0, Currency::EUROPEAN_EURO, $card, self::RETURN_URL, $customerId);
        $payment = $authorize->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        $newCustomer = $payment->getCustomer();
        $this->assertNotNull($newCustomer);
        $this->assertNotNull($newCustomer->getId());
    }
}
