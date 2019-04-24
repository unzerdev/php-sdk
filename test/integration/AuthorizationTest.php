<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the authorization transaction type.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class AuthorizationTest extends BasePaymentTest
{
    /**
     * Verify heidelpay object can perform an authorization based on the paymentTypeId.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeWithTypeId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $card->getId(), self::RETURN_URL);
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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizeWithType()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $card, self::RETURN_URL);
        $this->assertNotNull($authorize);
        $this->assertNotNull($authorize->getId());
    }

    /**
     * Verify authorization produces Payment and Customer.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizationProducesPaymentAndCustomer()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $this->assertNull($customer->getId());

        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $card, self::RETURN_URL, $customer);
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
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizationWithCustomerId(): Authorization
    {
        $card       = $this->heidelpay->createPaymentType($this->createCardObject());
        $customerId = $this->heidelpay->createCustomer($this->getMinimalCustomer())->getId();
        $orderId    = microtime(true);
        $authorize  = $this->heidelpay
            ->authorize(100.0, 'EUR', $card, self::RETURN_URL, $customerId, $orderId);
        $payment    = $authorize->getPayment();
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
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws RuntimeException
     */
    public function authorizationCanBeFetched(Authorization $authorization)
    {
        $fetchedAuthorization = $this->heidelpay->fetchAuthorization($authorization->getPaymentId());
        $this->assertEquals($authorization->expose(), $fetchedAuthorization->expose());
    }

    /**
     * Verify authorization has the expected states.
     *
     * @test
     * @dataProvider authorizeHasExpectedStatesDP
     *
     * @param BasePaymentType|AbstractHeidelpayResource $paymentType
     * @param bool                                      $isSuccess
     * @param bool                                      $isPending
     * @param bool                                      $isError
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizeHasExpectedStates(BasePaymentType $paymentType, $isSuccess, $isPending, $isError)
    {
        $paymentType = $this->heidelpay->createPaymentType($paymentType);
        $authorize = $this->heidelpay
            ->authorize(100.0, 'EUR', $paymentType->getId(), self::RETURN_URL, null, null, null, null, false);
        $this->assertEquals($isSuccess, $authorize->isSuccess());
        $this->assertEquals($isPending, $authorize->isPending());
        $this->assertEquals($isError, $authorize->isError());
    }

    /**
     * @return array
     *
     * @throws RuntimeException
     */
    public function authorizeHasExpectedStatesDP(): array
    {
        return [
            'card' => [$this->createCardObject(), true, false, false],
            'paypal' => [new Paypal(), false, true, false]
        ];
    }
}
