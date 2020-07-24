<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\unit
 */
namespace heidelpayPHP\test\unit;

use DateTime;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Services\CancelService;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\PaymentService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\WebhookService;
use heidelpayPHP\test\BasePaymentTest;
use heidelpayPHP\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

class HeidelpayTest extends BasePaymentTest
{
    /**
     * Verify constructor works properly.
     *
     * @test
     */
    public function constructorShouldInitPropertiesProperly(): void
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $paymentService = $heidelpay->getPaymentService();
        $this->assertInstanceOf(PaymentService::class, $paymentService);
        $this->assertInstanceOf(WebhookService::class, $heidelpay->getWebhookService());
        /** @var PaymentService $paymentService */
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertEquals('s-priv-1234', $heidelpay->getKey());
        $this->assertEquals(null, $heidelpay->getLocale());

        $heidelpaySwiss = new Heidelpay('s-priv-1234', 'de-CH');
        $this->assertEquals('de-CH', $heidelpaySwiss->getLocale());

        $heidelpayGerman = new Heidelpay('s-priv-1234', 'de-DE');
        $this->assertEquals('de-DE', $heidelpayGerman->getLocale());
    }

    /**
     * Verify getters and setters work properly.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): void
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $heidelpay->setLocale('myLocale');
        $this->assertEquals('myLocale', $heidelpay->getLocale());

        try {
            $heidelpay->setKey('this is not a valid key');
            $this->assertTrue(false, 'This exception should have been thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals('Illegal key: Use a valid private key with this SDK!', $e->getMessage());
        }

        $httpService = new HttpService();
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
     * @dataProvider resourceServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     */
    public function heidelpayShouldForwardResourceActionCallsToTheResourceService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ): void {
        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods([$serviceMethod])->getMock();
        $resourceSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = (new Heidelpay('s-priv-234'))->setResourceService($resourceSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    /**
     * Verify heidelpay propagates payment actions to the payment service.
     *
     * @test
     * @dataProvider paymentServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     */
    public function heidelpayShouldForwardPaymentActionCallsToThePaymentService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ): void {
        /** @var PaymentService|MockObject $paymentSrvMock */
        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->disableOriginalConstructor()->setMethods([$serviceMethod])->getMock();
        $paymentSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = (new Heidelpay('s-priv-234'))->setPaymentService($paymentSrvMock);

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
     */
    public function heidelpayShouldForwardWebhookActionCallsToTheWebhookService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ): void {
        /** @var WebhookService|MockObject $webhookSrvMock */
        $webhookSrvMock = $this->getMockBuilder(WebhookService::class)->disableOriginalConstructor()->setMethods([$serviceMethod])->getMock();
        $webhookSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = (new Heidelpay('s-priv-234'))->setWebhookService($webhookSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    /**
     * Verify heidelpay propagates cancel actions to the cancel service.
     *
     * @test
     * @dataProvider cancelServiceDP
     *
     * @param string $heidelpayMethod
     * @param array  $heidelpayParams
     * @param string $serviceMethod
     * @param array  $serviceParams
     */
    public function heidelpayShouldForwardCancelActionCallsToTheCancelService(
        $heidelpayMethod,
        array $heidelpayParams,
        $serviceMethod,
        array $serviceParams
    ): void {
        /** @var CancelService|MockObject $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods([$serviceMethod])->getMock();
        $cancelSrvMock->expects($this->once())->method($serviceMethod)->with(...$serviceParams);
        $heidelpay = (new Heidelpay('s-priv-234'))->setCancelService($cancelSrvMock);

        $heidelpay->$heidelpayMethod(...$heidelpayParams);
    }

    //<editor-fold desc="DataProviders">

    /**
     * Provide test data for heidelpayShouldForwardResourceActionCallsToTheResourceService.
     *
     * @return array
     */
    public static function resourceServiceDP(): array
    {
        $customerId     = 'customerId';
        $basketId       = 'basketId';
        $paymentId      = 'paymentId';
        $chargeId       = 'chargeId';
        $cancelId       = 'cancelId';
        $metadataId     = 'metaDataId';
        $orderId        = 'orderId';
        $paymentTypeId  = 'paymentTypeId';
        $customer       = new Customer();
        $basket         = new Basket();
        $payment        = new Payment();
        $sofort         = new Sofort();
        $card           = new Card('', '03/33');
        $auth           = new Authorization();
        $charge         = new Charge();
        $metadata       = new Metadata();

        return [
            'fetchPayment'                 => ['fetchPayment', [$payment], 'fetchPayment', [$payment]],
            'fetchPaymentByOrderId'        => ['fetchPaymentByOrderId', [$orderId], 'fetchPaymentByOrderId', [$orderId]],
            'fetchPaymentStr'              => ['fetchPayment', [$paymentId], 'fetchPayment', [$paymentId]],
            'fetchKeypair'                 => ['fetchKeypair', [], 'fetchKeypair', []],
            'createMetadata'               => ['createMetadata', [$metadata], 'createMetadata', [$metadata]],
            'fetchMetadata'                => ['fetchMetadata', [$metadata], 'fetchMetadata', [$metadata]],
            'fetchMetadataStr'             => ['fetchMetadata', [$metadataId], 'fetchMetadata', [$metadataId]],
            'createPaymentType'            => ['createPaymentType', [$sofort], 'createPaymentType', [$sofort]],
            'fetchPaymentType'             => ['fetchPaymentType', [$paymentTypeId], 'fetchPaymentType', [$paymentTypeId]],
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
            'fetchCharge'                  => ['fetchCharge', [$charge], 'fetchCharge', [$charge]],
            'fetchReversalByAuthorization' => ['fetchReversalByAuthorization', [$auth, $cancelId], 'fetchReversalByAuthorization', [$auth, $cancelId]],
            'fetchReversal'                => ['fetchReversal', [$payment, $cancelId], 'fetchReversal', [$payment, $cancelId]],
            'fetchReversalStr'             => ['fetchReversal', [$paymentId, $cancelId], 'fetchReversal', [$paymentId, $cancelId]],
            'fetchRefundById'              => ['fetchRefundById', [$payment, $chargeId, $cancelId], 'fetchRefundById', [$payment, $chargeId, $cancelId]],
            'fetchRefundByIdStr'           => ['fetchRefundById', [$paymentId, $chargeId, $cancelId], 'fetchRefundById', [$paymentId, $chargeId, $cancelId]],
            'fetchRefund'                  => ['fetchRefund', [$charge, $cancelId], 'fetchRefund', [$charge, $cancelId]],
            'fetchShipment'                => ['fetchShipment', [$payment, 'shipId'], 'fetchShipment', [$payment, 'shipId']],
            'activateRecurring'            => ['activateRecurringPayment', [$card, 'returnUrl'], 'activateRecurringPayment', [$card, 'returnUrl']],
            'activateRecurringWithId'      => ['activateRecurringPayment', [$paymentTypeId, 'returnUrl'], 'activateRecurringPayment', [$paymentTypeId, 'returnUrl']],
            'fetchPayout'                  => ['fetchPayout', [$payment], 'fetchPayout', [$payment]],
            'updatePaymentType'            => ['updatePaymentType', [$card], 'updatePaymentType', [$card]]
        ];
    }

    /**
     * Provide test data for heidelpayShouldForwardPaymentActionCallsToThePaymentService.
     *
     * @return array
     */
    public static function paymentServiceDP(): array
    {
        $url           = 'https://dev.heidelpay.com';
        $orderId       = 'orderId';
        $paymentTypeId = 'paymentTypeId';
        $customerId    = 'customerId';
        $paymentId     = 'paymentId';
        $customer      = new Customer();
        $sofort        = new Sofort();
        $metadata      = new Metadata();
        $payment       = new Payment();
        $paypage       = new Paypage(123.1234, 'EUR', 'url');
        $basket        = new Basket();
        $today         = new DateTime();

        return [
            'auth'                   => ['authorize', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata], 'authorize', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata]],
            'authAlt'                => ['authorize', [234.1, 'DZD', $sofort, $url], 'authorize', [234.1, 'DZD', $sofort, $url]],
            'authStr'                => ['authorize', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId], 'authorize', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId]],
            'charge'                 => ['charge', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata], 'charge', [1.234, 'AFN', $sofort, $url, $customer, $orderId, $metadata]],
            'chargeAlt'              => ['charge', [234.1, 'DZD', $sofort, $url], 'charge', [234.1, 'DZD', $sofort, $url]],
            'chargeStr'              => ['charge', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId], 'charge', [34.12, 'DKK', $paymentTypeId, $url, $customerId, $orderId]],
            'chargeAuth'             => ['chargeAuthorization', [$payment, 1.234], 'chargeAuthorization', [$payment, 1.234]],
            'chargeAuthAlt'          => ['chargeAuthorization', [$paymentId], 'chargeAuthorization', [$paymentId, null]],
            'chargeAuthStr'          => ['chargeAuthorization', [$paymentId, 2.345], 'chargeAuthorization', [$paymentId, 2.345]],
            'chargePayment'          => ['chargePayment', [$payment, 1.234, 'ALL'], 'chargePayment', [$payment, 1.234, 'ALL']],
            'chargePaymentAlt'       => ['chargePayment', [$payment], 'chargePayment', [$payment]],
            'ship'                   => ['ship', [$payment], 'ship', [$payment]],
            'payout'                 => ['payout', [123, 'EUR', $paymentTypeId, 'url', $customer, $orderId, $metadata, 'basketId'], 'payout', [123, 'EUR', $paymentTypeId, 'url', $customer, $orderId, $metadata, 'basketId']],
            'initPayPageCharge'      => ['initPayPageCharge', [$paypage, $customer, $basket, $metadata], 'initPayPageCharge', [$paypage, $customer, $basket, $metadata]],
            'initPayPageAuthorize'   => ['initPayPageAuthorize', [$paypage, $customer, $basket, $metadata], 'initPayPageAuthorize', [$paypage, $customer, $basket, $metadata]],
            'fetchDDInstalmentPlans' => ['fetchDirectDebitInstalmentPlans', [123.4567, 'EUR', 4.99, $today], 'fetchDirectDebitInstalmentPlans', [123.4567, 'EUR', 4.99, $today]]
        ];
    }

    /**
     * Provide test data for heidelpayShouldForwardWebhookActionCallsToTheWebhookService.
     *
     * @return array
     */
    public static function heidelpayShouldForwardWebhookActionCallsToTheWebhookServiceDP(): array
    {
        $url       = 'https://dev.heidelpay.com';
        $webhookId = 'webhookId';
        $webhook   = new Webhook();
        $event     = ['event1', 'event2'];

        return [
            'createWebhook'=> [ 'createWebhook', [$url, 'event'], 'createWebhook', [$url, 'event'] ],
            'fetchWebhook'=> [ 'fetchWebhook', [$webhookId], 'fetchWebhook', [$webhookId] ],
            'fetchWebhook by object'=> [ 'fetchWebhook', [$webhook], 'fetchWebhook', [$webhook] ],
            'updateWebhook'=> [ 'updateWebhook', [$webhook], 'updateWebhook', [$webhook] ],
            'deleteWebhook'=> [ 'deleteWebhook', [$webhookId], 'deleteWebhook', [$webhookId] ],
            'deleteWebhook by object'=> [ 'deleteWebhook', [$webhook], 'deleteWebhook', [$webhook] ],
            'fetchAllWebhooks'=> [ 'fetchAllWebhooks', [], 'fetchAllWebhooks', [] ],
            'deleteAllWebhooks'=> [ 'deleteAllWebhooks', [], 'deleteAllWebhooks', [] ],
            'registerMultipleWebhooks'=> ['registerMultipleWebhooks', [$url, $event], 'registerMultipleWebhooks', [$url, $event] ],
            'fetchResourceFromEvent'=> ['fetchResourceFromEvent', [], 'fetchResourceFromEvent', [] ]
        ];
    }

    /**
     * @return array
     */
    public static function cancelServiceDP(): array
    {
        $payment       = new Payment();
        $charge        = new Charge();
        $authorization = new Authorization();
        $chargeId      = 'chargeId';
        $paymentId      = 'paymentId';

        return [
            'cancelAuth'             => ['cancelAuthorization', [$authorization, 1.234], 'cancelAuthorization', [$authorization, 1.234]],
            'cancelAuthAlt'          => ['cancelAuthorization', [$authorization], 'cancelAuthorization', [$authorization]],
            'cancelAuthByPayment'    => ['cancelAuthorizationByPayment', [$payment, 1.234], 'cancelAuthorizationByPayment', [$payment, 1.234]],
            'cancelAuthByPaymentAlt' => ['cancelAuthorizationByPayment', [$payment], 'cancelAuthorizationByPayment', [$payment]],
            'cancelAuthByPaymentStr' => ['cancelAuthorizationByPayment', [$paymentId, 234.5], 'cancelAuthorizationByPayment', [$paymentId, 234.5]],
            'cancelChargeById'       => ['cancelChargeById', [$paymentId, $chargeId, 1.234], 'cancelChargeById', [$paymentId, $chargeId, 1.234]],
            'cancelChargeByIdAlt'    => ['cancelChargeById', [$paymentId, $chargeId], 'cancelChargeById', [$paymentId, $chargeId]],
            'cancelCharge'           => ['cancelCharge', [$charge, 1.234], 'cancelCharge', [$charge, 1.234]],
            'cancelChargeAlt'        => ['cancelCharge', [$charge], 'cancelCharge', [$charge]],
        ];
    }

    //</editor-fold>
}
