<?php
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;

class BasketTest extends BasePaymentTest
{
    /**
     * Verify basket can be created and fetched.
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     *
     * @group skip
     */
    public function minBasketShouldBeCreatableAndFetchable()
    {
        $orderId = microtime(true);
        $basket = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basket->addBasketItem(new BasketItem('myItem', 1234, 2345, 3456, 12));
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
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function maxBasketShouldBeCreatableAndFetchableWorkAround()
    {
        $basket = new Basket($this->generateOrderId(), 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 1234, 2345, 3456, 12))
            ->setBasketItemReferenceId('refId')
            ->setAmountVat(1.24)
            ->setVat(19)
            ->setUnit('ert')
            ->setAmountDiscount(1234.9);
        $basket->addBasketItem($basketItem);
        $this->assertEmpty($basket->getId());

        $this->heidelpay->createBasket($basket);
        $this->assertNotEmpty($basket->getId());

        $fetchedBasket = $this->heidelpay->fetchBasket($basket->getId());
        $this->assertEquals($basket->expose(), $fetchedBasket->expose());
        $this->assertEquals('This basket is creatable!', $basket->getNote());
    }

    /**
     * Verify the Basket can be updated.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     *
     * @group skip
     */
    public function basketShouldBeUpdatateable()
    {
        $orderId = $this->generateOrderId();
        $basket  = new Basket($orderId, 123.4, 'EUR', []);
        $basket->setNote('This basket is creatable!');
        $basketItem = (new BasketItem('myItem', 1234, 2345, 3456, 12))->setBasketItemReferenceId('refId');
        $basket->addBasketItem($basketItem);
        $this->heidelpay->createBasket($basket);

        $fetchedBasket = $this->heidelpay->fetchBasket($basket->getId());

        $fetchedBasket->setAmountTotal(4321);
        $fetchedBasket->setAmountTotalDiscount(5432);
        $fetchedBasket->setCurrencyCode('USD');
        $fetchedBasket->setNote('This basket is updateable!');
        $this->heidelpay->updateBasket($fetchedBasket);

        $this->heidelpay->fetchBasket($basket);
        $this->assertEquals($orderId, $basket->getOrderId());
        $this->assertEquals('USD', $basket->getCurrencyCode());
        $this->assertEquals(4321, $basket->getAmountTotal());
        $this->assertEquals(5432, $basket->getAmountTotalDiscount());
        $this->assertEquals('This basket is updateable!', $basket->getNote());
        $this->assertNotEquals($basket->getBasketItemByIndex(0)->expose(), $basketItem->expose());
    }
}
