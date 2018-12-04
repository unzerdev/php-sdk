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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit;

use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Constants\SupportedLocales;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Metadata;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Services\HttpService;
use heidelpay\MgwPhpSdk\Services\PaymentService;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\Services\DummyDebugHandler;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

class HeidelpayTest extends BaseUnitTest
{
    /**
     * Verify constructor works properly.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function constructorShouldInitPropertiesProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $this->assertInstanceOf(ResourceService::class, $heidelpay->getResourceService());
        $this->assertInstanceOf(PaymentService::class, $heidelpay->getPaymentService());
        $this->assertSame($heidelpay, $heidelpay->getPaymentService()->getHeidelpay());
        $this->assertEquals('s-priv-1234', $heidelpay->getKey());
        $this->assertEquals(SupportedLocales::USA_ENGLISH, $heidelpay->getLocale());

        $heidelpaySwiss = new Heidelpay('s-priv-1234', SupportedLocales::SWISS_GERMAN);
        $this->assertEquals(SupportedLocales::SWISS_GERMAN, $heidelpaySwiss->getLocale());

        $heidelpayGerman = new Heidelpay('s-priv-1234', SupportedLocales::GERMAN_GERMAN);
        $this->assertEquals(SupportedLocales::GERMAN_GERMAN, $heidelpayGerman->getLocale());
    }

    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $heidelpay->setLocale('myLocale');
        $this->assertEquals('myLocale', $heidelpay->getLocale());

        try {
            $heidelpay->setKey('söiodufhreoöhf');
            $this->assertTrue(false, 'This exception should have been thrown');
        } catch (\RuntimeException $e) {
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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

    //<editor-fold desc="DataProvider">

    /**
     * Provide test data for heidelpayShouldForwardResourceActionCallsToTheResourceService.
     *
     * @return array
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function heidelpayShouldForwardResourceActionCallsToTheResourceServiceDP(): array
    {
        $customerId   = 'customerId';
        $paymentId    = 'paymentId';
        $chargeId     = 'chargeId';
        $cancelId     = 'cancelId';
        $metadataId   = 'metaDataId';
        $customer     = new Customer();
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
            'fetchPaymentStr'              => ['fetchPayment', [$paymentId], 'fetchPayment', [$paymentId]],
            'fetchKeypair'                 => ['fetchKeypair', [], 'fetchKeypair', []],
            'fetchMetadata'                => ['fetchMetadata', [$metadata], 'fetchMetadata', [$metadata]],
            'fetchMetadataStr'             => ['fetchMetadata', [$metadataId], 'fetchMetadata', [$metadataId]],
            'createPaymentType'            => ['createPaymentType', [$sofort], 'createPaymentType', [$sofort]],
            'fetchPaymentType'             => ['fetchPaymentType', [$sofort], 'fetchPaymentType', [$sofort]],
            'createCustomer'               => ['createCustomer', [$customer], 'createCustomer', [$customer]],
            'createOrUpdateCustomer'       => [
                'createOrUpdateCustomer',
                [$customer],
                'createOrUpdateCustomer',
                [$customer]
            ],
            'fetchCustomer'                => ['fetchCustomer', [$customer], 'fetchCustomer', [$customer]],
            'fetchCustomerStr'             => ['fetchCustomer', [$customerId], 'fetchCustomer', [$customerId]],
            'updateCustomer'               => ['updateCustomer', [$customer], 'updateCustomer', [$customer]],
            'deleteCustomer'               => ['deleteCustomer', [$customer], 'deleteCustomer', [$customer]],
            'deleteCustomerStr'            => ['deleteCustomer', [$customerId], 'deleteCustomer', [$customerId]],
            'fetchAuthorization'           => ['fetchAuthorization', [$payment], 'fetchAuthorization', [$payment]],
            'fetchAuthorizationStr'        => ['fetchAuthorization', [$paymentId], 'fetchAuthorization', [$paymentId]],
            'fetchChargeById'              => [
                'fetchChargeById',
                [$paymentId, $chargeId],
                'fetchChargeById',
                [$paymentId, $chargeId]
            ],
            'fetchCharge'                  => ['fetchCharge', [$charge], 'fetch', [$charge]],
            'fetchReversalByAuthorization' => [
                'fetchReversalByAuthorization',
                [$auth, $cancelId],
                'fetchReversalByAuthorization',
                [$auth, $cancelId]
            ],
            'fetchReversal'                => [
                'fetchReversal',
                [$payment, $cancelId],
                'fetchReversal',
                [$payment, $cancelId]
            ],
            'fetchReversalStr'             => [
                'fetchReversal',
                [$paymentId, $cancelId],
                'fetchReversal',
                [$paymentId, $cancelId]
            ],
            'fetchRefundById'              => [
                'fetchRefundById',
                [$payment, $chargeId, $cancelId],
                'fetchRefundById',
                [$payment, $chargeId, $cancelId]
            ],
            'fetchRefundByIdStr'           => [
                'fetchRefundById',
                [$paymentId, $chargeId, $cancelId],
                'fetchRefundById',
                [$paymentId, $chargeId, $cancelId]
            ],
            'fetchRefund'                  => ['fetchRefund', [$chargeMock, $cancelId], 'fetch', [$cancellation]],
            'fetchShipment'                => [
                'fetchShipment',
                [$payment, 'shipId'],
                'fetchShipment',
                [$payment, 'shipId']
            ]
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
            'authorize'                       => [
                'authorize',
                [1.234, Currencies::AFGHAN_AFGHANI, $sofort, $url, $customer, $orderId, $metadata],
                'authorize',
                [1.234, Currencies::AFGHAN_AFGHANI, $sofort, $url, $customer, $orderId, $metadata]
            ],
            'authorizeAlt'                    => [
                'authorize',
                [234.1, Currencies::ALGERIAN_DINAR, $sofort, $url],
                'authorize',
                [234.1, Currencies::ALGERIAN_DINAR, $sofort, $url]
            ],
            'authorizeStr'                    => [
                'authorize',
                [34.12, Currencies::DANISH_KRONE, $paymentTypeId, $url, $customerId, $orderId],
                'authorize',
                [34.12, Currencies::DANISH_KRONE, $paymentTypeId, $url, $customerId, $orderId]
            ],
            'authorizeWithPayment'            => [
                'authorizeWithPayment',
                [1.234, Currencies::AFGHAN_AFGHANI, $payment, $url, $customer, $orderId, $metadata],
                'authorizeWithPayment',
                [1.234, Currencies::AFGHAN_AFGHANI, $payment, $url, $customer, $orderId, $metadata]
            ],
            'authorizeWithPaymentStr'         => [
                'authorizeWithPayment',
                [34.12, Currencies::DANISH_KRONE, $payment, $url, $customerId, $orderId],
                'authorizeWithPayment',
                [34.12, Currencies::DANISH_KRONE, $payment, $url, $customerId, $orderId]
            ],
            'charge'                          => [
                'charge',
                [1.234, Currencies::AFGHAN_AFGHANI, $sofort, $url, $customer, $orderId, $metadata],
                'charge',
                [1.234, Currencies::AFGHAN_AFGHANI, $sofort, $url, $customer, $orderId, $metadata]
            ],
            'chargeAlt'                       => [
                'charge',
                [234.1, Currencies::ALGERIAN_DINAR, $sofort, $url],
                'charge',
                [234.1, Currencies::ALGERIAN_DINAR, $sofort, $url]
            ],
            'chargeStr'                       => [
                'charge',
                [34.12, Currencies::DANISH_KRONE, $paymentTypeId, $url, $customerId, $orderId],
                'charge',
                [34.12, Currencies::DANISH_KRONE, $paymentTypeId, $url, $customerId, $orderId]
            ],
            'chargeAuthorization'             => [
                'chargeAuthorization',
                [$payment, 1.234],
                'chargeAuthorization',
                [$payment, 1.234]
            ],
            'chargeAuthorizationAlt'          => [
                'chargeAuthorization',
                [$paymentId],
                'chargeAuthorization',
                [$paymentId, null]
            ],
            'chargeAuthorizationStr'          => [
                'chargeAuthorization',
                [$paymentId, 2.345],
                'chargeAuthorization',
                [$paymentId, 2.345]
            ],
            'chargePayment'                   => [
                'chargePayment',
                [$payment, 1.234, Currencies::ALBANIAN_LEK],
                'chargePayment',
                [$payment, 1.234, Currencies::ALBANIAN_LEK]
            ],
            'chargePaymentAlt'                => [
                'chargePayment',
                [$payment],
                'chargePayment',
                [$payment]
            ],
            'cancelAuthorization'             => [
                'cancelAuthorization',
                [$authorization, 1.234],
                'cancelAuthorization',
                [$authorization, 1.234]
            ],
            'cancelAuthorizationAlt'          => [
                'cancelAuthorization',
                [$authorization],
                'cancelAuthorization',
                [$authorization]
            ],
            'cancelAuthorizationByPayment'    => [
                'cancelAuthorizationByPayment',
                [$payment, 1.234],
                'cancelAuthorizationByPayment',
                [$payment, 1.234]
            ],
            'cancelAuthorizationByPaymentAlt' => [
                'cancelAuthorizationByPayment',
                [$payment],
                'cancelAuthorizationByPayment',
                [$payment]
            ],
            'cancelAuthorizationByPaymentStr' => [
                'cancelAuthorizationByPayment',
                [$paymentId, 234.5],
                'cancelAuthorizationByPayment',
                [$paymentId, 234.5]
            ],
            'cancelChargeById'                => [
                'cancelChargeById',
                [$paymentId, $chargeId, 1.234],
                'cancelChargeById',
                [$paymentId, $chargeId, 1.234]
            ],
            'cancelChargeByIdAlt'             => [
                'cancelChargeById',
                [$paymentId, $chargeId],
                'cancelChargeById',
                [$paymentId, $chargeId]
            ],
            'cancelCharge'                    => ['cancelCharge', [$charge, 1.234], 'cancelCharge', [$charge, 1.234]],
            'cancelChargeAlt'                 => ['cancelCharge', [$charge], 'cancelCharge', [$charge]],
            'ship'                            => ['ship', [$payment], 'ship', [$payment]],
            'shipStr'                         => ['ship', [$paymentId], 'ship', [$paymentId]]
        ];
    }

    //</editor-fold>
}
