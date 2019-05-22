<?php /** @noinspection UnnecessaryAssertionInspection */

/**
 * This class defines unit tests to verify functionality of the heidelpay class.
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
namespace heidelpayPHP\test\unit;

use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\PaymentService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\WebhookService;
use heidelpayPHP\test\BaseUnitTest;
use heidelpayPHP\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;

class HeidelpayTest extends BaseUnitTest
{
    /**
     * Verify constructor works properly.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function constructorShouldInitPropertiesProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $this->assertInstanceOf(ResourceService::class, $heidelpay->getResourceService());
        $this->assertInstanceOf(PaymentService::class, $heidelpay->getPaymentService());
        $this->assertInstanceOf(WebhookService::class, $heidelpay->getWebhookService());
        $this->assertSame($heidelpay, $heidelpay->getPaymentService()->getHeidelpay());
        $this->assertEquals('s-priv-1234', $heidelpay->getKey());
        $this->assertEquals(null, $heidelpay->getLocale());

        $heidelpaySwiss = new Heidelpay('s-priv-1234', 'de_CH');
        $this->assertEquals('de_CH', $heidelpaySwiss->getLocale());

        $heidelpayGerman = new Heidelpay('s-priv-1234', 'de_DE');
        $this->assertEquals('de_DE', $heidelpayGerman->getLocale());
    }

    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $heidelpay->setLocale('myLocale');
        $this->assertEquals('myLocale', $heidelpay->getLocale());

        try {
            $heidelpay->setKey('söiodufhreoöhf');
            $this->assertTrue(false, 'This exception should have been thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals('Illegal key: Use a valid private key with this SDK!', $e->getMessage());
        }

        $httpService = new HttpService();
        $this->assertInstanceOf(HttpService::class, $heidelpay->getHttpService());
        $this->assertNotSame($httpService, $heidelpay->getHttpService());
        $heidelpay->setHttpService($httpService);
        $this->assertSame($httpService, $heidelpay->getHttpService());

        $resourceSrv = new ResourceService($heidelpay);
        $heidelpay->setResourceService($resourceSrv);
        $this->assertSame($resourceSrv, $heidelpay->getResourceService());

        $paymentSrv = new PaymentService($heidelpay);
        $heidelpay->setPaymentService($paymentSrv);
        $this->assertSame($paymentSrv, $heidelpay->getPaymentService());

        $webhookSrv = new WebhookService($heidelpay);
        $heidelpay->setWebhookService($webhookSrv);
        $this->assertSame($webhookSrv, $heidelpay->getWebhookService());

        $this->assertFalse($heidelpay->isDebugMode());
        $heidelpay->setDebugMode(true);
        $this->assertTrue($heidelpay->isDebugMode());
        $heidelpay->setDebugMode(false);
        $this->assertFalse($heidelpay->isDebugMode());

        $this->assertNull($heidelpay->getDebugHandler());
        $dummyDebugHandler = new DummyDebugHandler();
        $heidelpay->setDebugHandler($dummyDebugHandler);
        $this->assertSame($dummyDebugHandler, $heidelpay->getDebugHandler());

        $this->assertEquals('', $heidelpay->getUri());
    }

    /**
     * Verify heidelpay propagates resource actions to the resource service.
     *
     * @test
     * @dataProvider heidelpayShouldForwardResourceActionCallsToTheResourceServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function heidelpayShouldForwardResourceActionCallsToTheResourceService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ) {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods([$serviceMethod])->getMock();

        $resourceSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = new Heidelpay('s-priv-234');

        /** @var ResourceService $resourceSrvMock */
        $heidelpay->setResourceService($resourceSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    /**
     * Verify heidelpay propagates payment actions to the payment service.
     *
     * @test
     * @dataProvider heidelpayShouldForwardPaymentActionCallsToThePaymentServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function heidelpayShouldForwardPaymentActionCallsToThePaymentService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ) {
        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->disableOriginalConstructor()
            ->setMethods([$serviceMethod])->getMock();

        $paymentSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = new Heidelpay('s-priv-234');

        /** @var PaymentService $paymentSrvMock */
        $heidelpay->setPaymentService($paymentSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    /**
     * Verify heidelpay propagates webhook actions to the webhook service.
     *
     * @test
     * @dataProvider heidelpayShouldForwardWebhookActionCallsToTheWebhookServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function heidelpayShouldForwardWebhookActionCallsToTheWebhookService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ) {
        $webhookSrvMock = $this->getMockBuilder(WebhookService::class)->disableOriginalConstructor()
            ->setMethods([$serviceMethod])->getMock();

        $webhookSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = new Heidelpay('s-priv-234');

        /** @var WebhookService $webhookSrvMock */
        $heidelpay->setWebhookService($webhookSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    //<editor-fold desc="DataProviders">

    /**
     * Provide test data for heidelpayShouldForwardResourceActionCallsToTheResourceService.
     *
     * @return array
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function heidelpayShouldForwardResourceActionCallsToTheResourceServiceDP(): array
    {
        $customerId   = 'customerId';
        $basketId     = 'basketId';
        $paymentId    = 'paymentId';
        $chargeId     = 'chargeId';
        $cancelId     = 'cancelId';
        $metadataId   = 'metaDataId';
        $orderId      = 'orderId';
        $customer     = new Customer();
        $basket       = new Basket();
        $payment      = new Payment();
        $sofort       = new Sofort();
        $auth         = new Authorization();
        $charge       = new Charge();
        $metadata     = new Metadata();
        $cancellation = new Cancellation();
        $chargeMock   = $this->getMockBuilder(Charge::class)->setMethods(['getCancellation'])->getMock();
        $chargeMock->expects($this->once())->method('getCancellation')->with($cancelId, true)->willReturn(
            $cancellation
        );

        return [
            'getResource'                  => ['getResource', [$customer], 'getResource', [$customer]],
            'fetchResource'                => ['fetchResource', [$customer], 'fetch', [$customer]],
            'fetchPayment'                 => ['fetchPayment', [$payment], 'fetchPayment', [$payment]],
            'fetchPaymentByOrderId'        => ['fetchPaymentByOrderId', [$orderId], 'fetchPaymentByOrderId', [$orderId]],
            'fetchPaymentStr'              => ['fetchPayment', [$paymentId], 'fetchPayment', [$paymentId]],
            'fetchKeypair'                 => ['fetchKeypair', [], 'fetchKeypair', []],
            'createMetadata'               => ['createMetadata', [$metadata], 'createMetadata', [$metadata]],
            'fetchMetadata'                => ['fetchMetadata', [$metadata], 'fetchMetadata', [$metadata]],
            'fetchMetadataStr'             => ['fetchMetadata', [$metadataId], 'fetchMetadata', [$metadataId]],
            'createPaymentType'            => ['createPaymentType', [$sofort], 'createPaymentType', [$sofort]],
            'fetchPaymentType'             => ['fetchPaymentType', [$sofort], 'fetchPaymentType', [$sofort]],
            'createCustomer'               => ['createCustomer', [$customer], 'createCustomer', [$customer]],
            'createOrUpdateCustomer'       => ['createOrUpdateCustomer', [$customer], 'createOrUpdateCustomer', [$customer]],
            'fetchCustomer'                => ['fetchCustomer', [$customer], 'fetchCustomer', [$customer]],
            'fetchCustomerByExtCustomerId' => ['fetchCustomerByExtCustomerId', [$customerId], 'fetchCustomerByExtCustomerId', [$customerId]],
            'fetchCustomerStr'             => ['fetchCustomer', [$customerId], 'fetchCustomer', [$customerId]],
            'updateCustomer'               => ['updateCustomer', [$customer], 'updateCustomer', [$customer]],
            'deleteCustomer'               => ['deleteCustomer', [$customer], 'deleteCustomer', [$customer]],
            'deleteCustomerStr'            => ['deleteCustomer', [$customerId], 'deleteCustomer', [$customerId]],
            'createBasket'                 => ['createBasket', [$basket], 'createBasket', [$basket]],
            'fetchBasket'                  => ['fetchBasket', [$basket], 'fetchBasket', [$basket]],
            'fetchBasketStr'               => ['fetchBasket', [$basketId], 'fetchBasket', [$basketId]],
            'updateBasket'                 => ['updateBasket', [$basket], 'updateBasket', [$basket]],
            'fetchAuthorization'           => ['fetchAuthorization', [$payment], 'fetchAuthorization', [$payment]],
            'fetchAuthorizationStr'        => ['fetchAuthorization', [$paymentId], 'fetchAuthorization', [$paymentId]],
            'fetchChargeById'              => ['fetchChargeById', [$paymentId, $chargeId], 'fetchChargeById', [$paymentId, $chargeId]],
            'fetchCharge'                  => ['fetchCharge', [$charge], 'fetch', [$charge]],
            'fetchReversalByAuthorization' => ['fetchReversalByAuthorization', [$auth, $cancelId], 'fetchReversalByAuthorization', [$auth, $cancelId]],
            'fetchReversal'                => ['fetchReversal', [$payment, $cancelId], 'fetchReversal', [$payment, $cancelId]],
            'fetchReversalStr'             => ['fetchReversal', [$paymentId, $cancelId], 'fetchReversal', [$paymentId, $cancelId]],
            'fetchRefundById'              => ['fetchRefundById', [$payment, $chargeId, $cancelId], 'fetchRefundById', [$payment, $chargeId, $cancelId]],
            'fetchRefundByIdStr'           => ['fetchRefundById', [$paymentId, $chargeId, $cancelId], 'fetchRefundById', [$paymentId, $chargeId, $cancelId]],
            'fetchRefund'                  => ['fetchRefund', [$chargeMock, $cancelId], 'fetch', [$cancellation]],
            'fetchShipment'                => ['fetchShipment', [$payment, 'shipId'], 'fetchShipment', [$payment, 'shipId']]
        ];
    }

    /**
     * Provide test data for heidelpayShouldForwardPaymentActionCallsToThePaymentService.
     *
     * @return array
     */
    public function heidelpayShouldForwardPaymentActionCallsToThePaymentServiceDP(): array
    {
        $url           = 'https://dev.heidelpay.com';
        $orderId       = 'orderId';
        $paymentTypeId = 'paymentTypeId';
        $customerId    = 'customerId';
        $paymentId     = 'paymentId';
        $chargeId      = 'chargeId';
        $customer      = new Customer();
        $sofort        = new Sofort();
        $metadata      = new Metadata();
        $payment       = new Payment();
        $authorization = new Authorization();
        $charge        = new Charge();

        return [
            'auth'                   => ['authorize', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata], 'authorize', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata]],
            'authAlt'                => ['authorize', [234.1, 'DZD', $sofort, $url], 'authorize', [234.1, 'DZD', $sofort, $url]],
            'authStr'                => ['authorize', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId], 'authorize', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId]],
            'authWithPayment'        => ['authorizeWithPayment', [1.234, 'AFN', $payment, $url, $customer, $orderId, $metadata], 'authorizeWithPayment', [1.234, 'AFN', $payment, $url, $customer, $orderId, $metadata]],
            'authWithPaymentStr'     => ['authorizeWithPayment', [34.12, 'DKK', $payment, $url, $customerId, $orderId], 'authorizeWithPayment', [34.12, 'DKK', $payment, $url, $customerId, $orderId]],
            'charge'                 => ['charge', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata], 'charge', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata]],
            'chargeAlt'              => ['charge', [234.1, 'DZD', $sofort, $url], 'charge', [234.1, 'DZD', $sofort, $url]],
            'chargeStr'              => ['charge', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId], 'charge', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId]],
            'chargeAuth'             => ['chargeAuthorization', [$payment, 1.234], 'chargeAuthorization', [$payment, 1.234]],
            'chargeAuthAlt'          => ['chargeAuthorization', [$paymentId], 'chargeAuthorization', [$paymentId, null]],
            'chargeAuthStr'          => ['chargeAuthorization', [$paymentId, 2.345], 'chargeAuthorization', [$paymentId, 2.345]],
            'chargePayment'          => ['chargePayment', [$payment, 1.234, 'ALL'], 'chargePayment', [$payment, 1.234, 'ALL']],
            'chargePaymentAlt'       => ['chargePayment', [$payment], 'chargePayment', [$payment]],
            'cancelAuth'             => ['cancelAuthorization', [$authorization, 1.234], 'cancelAuthorization', [$authorization, 1.234]],
            'cancelAuthAlt'          => ['cancelAuthorization', [$authorization], 'cancelAuthorization', [$authorization]],
            'cancelAuthByPayment'    => ['cancelAuthorizationByPayment', [$payment, 1.234], 'cancelAuthorizationByPayment', [$payment, 1.234]],
            'cancelAuthByPaymentAlt' => ['cancelAuthorizationByPayment', [$payment], 'cancelAuthorizationByPayment', [$payment]],
            'cancelAuthByPaymentStr' => ['cancelAuthorizationByPayment', [$paymentId, 234.5], 'cancelAuthorizationByPayment', [$paymentId, 234.5]],
            'cancelChargeById'       => ['cancelChargeById', [$paymentId, $chargeId, 1.234], 'cancelChargeById', [$paymentId, $chargeId, 1.234]],
            'cancelChargeByIdAlt'    => ['cancelChargeById', [$paymentId, $chargeId], 'cancelChargeById', [$paymentId, $chargeId]],
            'cancelCharge'           => ['cancelCharge', [$charge, 1.234], 'cancelCharge', [$charge, 1.234]],
            'cancelChargeAlt'        => ['cancelCharge', [$charge], 'cancelCharge', [$charge]],
            'ship'                   => ['ship', [$payment], 'ship', [$payment]]
        ];
    }

    /**
     * Provide test data for heidelpayShouldForwardWebhookActionCallsToTheWebhookService.
     *
     * @return array
     */
    public function heidelpayShouldForwardWebhookActionCallsToTheWebhookServiceDP(): array
    {
        $url           = 'https://dev.heidelpay.com';
        $webhookId     = 'webhookId';
        $webhook     = new Webhook();
        $event = ['event1', 'event2'];

        return [
            'createWebhook'=> [ 'createWebhook', [$url, 'event'], 'createWebhook', [$url, 'event'] ],
            'fetchWebhook'=> [ 'fetchWebhook', [$webhookId], 'fetchWebhook', [$webhookId] ],
            'fetchWebhook by object'=> [ 'fetchWebhook', [$webhook], 'fetchWebhook', [$webhook] ],
            'updateWebhook'=> [ 'updateWebhook', [$webhook], 'updateWebhook', [$webhook] ],
            'deleteWebhook'=> [ 'deleteWebhook', [$webhookId], 'deleteWebhook', [$webhookId] ],
            'deleteWebhook by object'=> [ 'deleteWebhook', [$webhook], 'deleteWebhook', [$webhook] ],
            'fetchAllWebhooks'=> [ 'fetchAllWebhooks', [], 'fetchWebhooks', [] ],
            'deleteAllWebhooks'=> [ 'deleteAllWebhooks', [], 'deleteWebhooks', [] ],
            'registerMultipleWebhooks'=> ['registerMultipleWebhooks', [$url, $event], 'createWebhooks', [$url, $event] ],
            'fetchResourceFromEvent'=> ['fetchResourceFromEvent', [], 'fetchResourceByWebhookEvent', [] ]
        ];
    }

    //</editor-fold>
}
