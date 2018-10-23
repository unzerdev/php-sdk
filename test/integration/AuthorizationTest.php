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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\integration\test;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\ExpectationFailedException;

class AuthorizationTest extends BasePaymentTest
{
    /**
     * Verify heidelpay object can perform an authorization based on the paymentTypeId.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     */
    public function authorizeWithTypeId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $this->heidelpay->authorize(
            100.0,
            Currencies::EURO,
            $card->getId(),
            self::RETURN_URL
        );
        $this->assertNotNull($authorize);
        $this->assertNotEmpty($authorize->getId());
        $this->assertNotEmpty($authorize->getUniqueId());
        $this->assertNotEmpty($authorize->getShortId());
    }

    /**
     * Verify heidelpay object can perform an authorization based on the paymentType object.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorizeWithType()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $this->heidelpay->authorize(100.0, Currencies::EURO, $card, self::RETURN_URL);
        $this->assertNotNull($authorize);
        $this->assertNotNull($authorize->getId());
    }

    /**
     * Verify authorization produces Payment and Customer.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorizationProducesPaymentAndCustomer()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $this->assertNull($customer->getId());

        $authorize = $this->heidelpay->authorize(100.0, Currencies::EURO, $card, self::RETURN_URL, $customer);
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
     *
     * @return Authorization
     *
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorizationWithCustomerId(): Authorization
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customerId = $this->heidelpay->createCustomer($this->getMinimalCustomer())->getId();
        $authorize = $this->heidelpay->authorize(100.0, Currencies::EURO, $card, self::RETURN_URL, $customerId, time());
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
     *
     * @param Authorization $authorization
     *
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \PHPUnit\Framework\Exception
     * @throws \RuntimeException
     */
    public function authorizationCanBeFetched(Authorization $authorization)
    {
        $fetchedAuthorization = $this->heidelpay->fetchAuthorization($authorization->getPaymentId());
        $this->assertEquals($authorization->expose(), $fetchedAuthorization->expose());
    }
}
