<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the authorization transaction type.
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
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */

namespace heidelpay\MgwPhpSdk\integration\test;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

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
     * @return Authorization
     */
    public function authorizationWithCustomerId(): Authorization
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

        return $authorize;
    }

    /**
     * Verify authorization can be fetched.
     *
     * @depends authorizationWithCustomerId
     * @test
     * @param Authorization $authorization
     */
    public function authorizationCanBeFetched(Authorization $authorization)
    {
        $fetchedAuthorization = $this->heidelpay->fetchAuthorization($authorization->getPayment()->getId());
        $this->assertEquals($authorization->expose(), $fetchedAuthorization->expose());
    }
}
