<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify Basket functionalities.
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
 * @package  heidelpayPHP\test\integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\test\BaseIntegrationTest;

class BasketTest extends BaseIntegrationTest
{
    /**
     * Verify basket can be created and fetched.
     *
     * @test
     */
    public function minBasketShouldBeCreatableAndFetchable(): void
    {
        $orderId = microtime(true);
        $basket = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = new BasketItem('myItem', 1234, 2345, 12);
        $basket->addBasketItem($basketItem);
        $basketItem = (new BasketItem('title'))->setAmountPerUnit(0.0);
        $basket->addBasketItem($basketItem);
        $this->assertEmpty($basket->getId());

        $this->heidelpay->createBasket($basket);
        $this->assertNotEmpty($basket->getId());

        $fetchedBasket = $this->heidelpay->fetchBasket($basket->getId());
        $this->assertEquals($basket->expose(), $fetchedBasket->expose());
        $this->assertEquals('This basket is creatable!', $basket->getNote());
    }

    /**
     * Verify basket can be created and fetched.
     *
     * @test
     */
    public function maxBasketShouldBeCreatableAndFetchableWorkAround(): void
    {
        $basket = new Basket('b' . self::generateRandomId(), 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 1234, 2345, 12))
            ->setBasketItemReferenceId('refId')
            ->setAmountVat(1.24)
            ->setVat(19)
            ->setUnit('ert')
            ->setAmountDiscount(1234.9)
            ->setImageUrl('https://files.readme.io/9f556bd-small-Heidelpay-Logo_mitUnterzeile-orange_RGB.jpg')
            ->setSubTitle('This is some subtitle for this item')
            ->setType('this is some type');
        $basket->addBasketItem($basketItem);
        $this->assertEmpty($basket->getId());

        $this->heidelpay->createBasket($basket);
        $this->assertNotEmpty($basket->getId());

        $fetchedBasket = $this->heidelpay->fetchBasket($basket->getId());
        $this->assertEquals($basket->expose(), $fetchedBasket->expose());
        $this->assertEquals(
            $basket->getBasketItemByIndex(0)->expose(),
            $fetchedBasket->getBasketItemByIndex(0)->expose()
        );
    }

    /**
     * Verify basketItem with invalid imageUrl will return an error.
     *
     * @test
     *
     * @dataProvider basketItemWithInvalidUrlWillThrowAnErrorDP
     *
     * @param $expectException
     * @param $imageUrl
     * @param null $exceptionCode
     */
    public function basketItemWithInvalidUrlWillThrowAnError($expectException, $imageUrl, $exceptionCode = null): void
    {
        $basket = new Basket('b' . self::generateRandomId(), 123.4, 'EUR', []);
        $basketItem = (new BasketItem('myItem', 1234, 2345, 12))->setImageUrl($imageUrl);
        $basket->addBasketItem($basketItem);

        try {
            $this->heidelpay->createBasket($basket);
            $this->assertFalse(
                $expectException,
                'Failed asserting that exception of type "heidelpayPHP\Exceptions\HeidelpayApiException" is thrown.'
            );
        } catch (HeidelpayApiException $e) {
            $this->assertTrue($expectException);
            $this->assertEquals($exceptionCode, $e->getCode());
            $this->assertNotNull($e->getErrorId());
        }
    }

    /**
     * Verify the Basket can be updated.
     *
     * @test
     */
    public function basketShouldBeUpdateable(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket  = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 1234, 2345, 12))->setBasketItemReferenceId('refId');
        $basket->addBasketItem($basketItem);
        $this->heidelpay->createBasket($basket);

        $fetchedBasket = $this->heidelpay->fetchBasket($basket->getId());
        $fetchedBasket->setAmountTotalGross(4321);
        $fetchedBasket->setAmountTotalDiscount(5432);
        $fetchedBasket->setNote('This basket is updateable!');
        $fetchedBasket->getBasketItemByIndex(0)->setTitle('This item can also be updated!');
        $this->heidelpay->updateBasket($fetchedBasket);

        $this->heidelpay->fetchBasket($basket);
        $this->assertEquals($orderId, $basket->getOrderId());
        $this->assertEquals(4321, $basket->getAmountTotalGross());
        $this->assertEquals(5432, $basket->getAmountTotalDiscount());
        $this->assertEquals('This basket is updateable!', $basket->getNote());
        $this->assertNotEquals($basket->getBasketItemByIndex(0)->expose(), $basketItem->expose());
    }

    /**
     * Verify basket can be passed to the payment on authorize.
     *
     * @test
     */
    public function authorizeTransactionsShouldPassAlongTheBasketIdIfSet(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket  = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 123.4, 234.5, 12))->setBasketItemReferenceId('refId');
        $basket->addBasketItem($basketItem);
        $this->heidelpay->createBasket($basket);
        $this->assertNotEmpty($basket->getId());

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $paypal->authorize(123.4, 'EUR', 'https://heidelpay.com', null, null, null, $basket);

        $fetchedPayment = $this->heidelpay->fetchPayment($authorize->getPaymentId());
        $this->assertEquals($basket->expose(), $fetchedPayment->getBasket()->expose());
    }

    /**
     * Verify basket can be passed to the payment on charge.
     *
     * @test
     */
    public function chargeTransactionsShouldPassAlongTheBasketIdIfSet(): void
    {
        $basket  = $this->createBasket();
        $this->assertNotEmpty($basket->getId());

        $sdd = (new SepaDirectDebit('DE89370400440532013000'))->setBic('COBADEFFXXX');
        $this->heidelpay->createPaymentType($sdd);

        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge   = $sdd->charge(119.0, 'EUR', self::RETURN_URL, $customer, null, null, $basket);

        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertEquals($basket->expose(), $fetchedPayment->getBasket()->expose());
    }

    /**
     * Verify basket will be created and passed to the payment on authorize if it does not exist yet.
     *
     * @test
     */
    public function authorizeTransactionsShouldCreateBasketIfItDoesNotExistYet(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket  = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 1234, 2345, 12))->setBasketItemReferenceId('refId');
        $basket->addBasketItem($basketItem);
        $this->assertEmpty($basket->getId());

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorize = $paypal->authorize(123.4, 'EUR', 'https://heidelpay.com', null, null, null, $basket);
        $this->assertNotEmpty($basket->getId());

        $fetchedPayment = $this->heidelpay->fetchPayment($authorize->getPaymentId());
        $this->assertEquals($basket->expose(), $fetchedPayment->getBasket()->expose());
    }

    /**
     * Verify basket will be created and passed to the payment on charge if it does not exist yet.
     *
     * @test
     */
    public function chargeTransactionsShouldCreateBasketIfItDoesNotExistYet(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket  = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basket->setAmountTotalVat(10.9);
        $basketItem = (new BasketItem('myItem', 1234, 2345, 12))->setBasketItemReferenceId('refId');
        $basket->addBasketItem($basketItem);
        $this->assertEmpty($basket->getId());

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $charge = $paypal->charge(123.4, 'EUR', 'https://heidelpay.com', null, null, null, $basket);
        $this->assertNotEmpty($basket->getId());

        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertEquals($basket->expose(), $fetchedPayment->getBasket()->expose());
    }

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function basketItemWithInvalidUrlWillThrowAnErrorDP(): array
    {
        return [
            'valid ' => [false, 'https://files.readme.io/9f556bd-small-Heidelpay-Logo_mitUnterzeile-orange_RGB.jpg'],
            'valid null' => [false, null],
            'valid empty' => [false, ''],
            'invalid not available' => [true, 'https://files.readme.io/does-not-exist.jpg', ApiResponseCodes::API_ERROR_BASKET_ITEM_IMAGE_INVALID_URL]
        ];
    }

    //</editor-fold>
}
