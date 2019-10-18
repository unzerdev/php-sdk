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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration/transaction_types
 */
namespace heidelpayPHP\test\integration\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Card;
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
        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $paymentType->getId(), self::RETURN_URL);
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
        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $paymentType, self::RETURN_URL);
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
        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $customer = $this->getMinimalCustomer();
        $this->assertNull($customer->getId());

        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $paymentType, self::RETURN_URL, $customer);
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
        $paymentType = $this->heidelpay->createPaymentType(new Paypal());
        $customerId  = $this->heidelpay->createCustomer($this->getMinimalCustomer())->getId();
        $orderId     = microtime(true);
        $authorize   = $this->heidelpay->authorize(100.0, 'EUR', $paymentType, self::RETURN_URL, $customerId, $orderId);
        $payment     = $authorize->getPayment();
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
     * @param string                                    $expectedState The state the transaction is expected to be in.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizeHasExpectedStates(BasePaymentType $paymentType, $expectedState)
    {
        $paymentType = $this->heidelpay->createPaymentType($paymentType);
        $authorize = $this->heidelpay->authorize(100.0, 'EUR', $paymentType->getId(), self::RETURN_URL, null, null, null, null, false);

        $stateCheck = 'assert' . ucfirst($expectedState);
        $this->$stateCheck($authorize);
    }

    /**
     * Verify authorize accepts all parameters.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizeShouldAcceptAllParameters()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $orderId = $this->generateRandomId();
        $metadata = (new Metadata())->addMetadata('key', 'value');
        $basket = $this->createBasket();
        $invoiceId = $this->generateRandomId();
        $paymentReference = 'paymentReference';

        $authorize = $card->authorize(123.4, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, true, $invoiceId, $paymentReference);
        $payment = $authorize->getPayment();

        $this->assertSame($card, $payment->getPaymentType());
        $this->assertEquals(123.4, $authorize->getAmount());
        $this->assertEquals('EUR', $authorize->getCurrency());
        $this->assertEquals(self::RETURN_URL, $authorize->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertEquals($orderId, $authorize->getOrderId());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertTrue($authorize->isCard3ds());
        $this->assertEquals($invoiceId, $authorize->getInvoiceId());
        $this->assertEquals($paymentReference, $authorize->getPaymentReference());

        $fetchedAuthorize = $this->heidelpay->fetchAuthorization($authorize->getPaymentId());
        $fetchedPayment = $fetchedAuthorize->getPayment();

        $this->assertEquals($payment->getPaymentType()->expose(), $fetchedPayment->getPaymentType()->expose());
        $this->assertEquals($authorize->getAmount(), $fetchedAuthorize->getAmount());
        $this->assertEquals($authorize->getCurrency(), $fetchedAuthorize->getCurrency());
        $this->assertEquals($authorize->getReturnUrl(), $fetchedAuthorize->getReturnUrl());
        $this->assertEquals($payment->getCustomer()->expose(), $fetchedPayment->getCustomer()->expose());
        $this->assertEquals($authorize->getOrderId(), $fetchedAuthorize->getOrderId());
        $this->assertEquals($payment->getMetadata()->expose(), $fetchedPayment->getMetadata()->expose());
        $this->assertEquals($payment->getBasket()->expose(), $fetchedPayment->getBasket()->expose());
        $this->assertEquals($authorize->isCard3ds(), $fetchedAuthorize->isCard3ds());
        $this->assertEquals($authorize->getInvoiceId(), $fetchedAuthorize->getInvoiceId());
        $this->assertEquals($authorize->getPaymentReference(), $fetchedAuthorize->getPaymentReference());
    }

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     *
     * @throws RuntimeException
     */
    public function authorizeHasExpectedStatesDP(): array
    {
        return [
            'card' => [$this->createCardObject(), 'success'],
            'paypal' => [new Paypal(), 'pending']
        ];
    }

    //</editor-fold>
}
