<?php
/**
 * This class defines unit tests to verify functionality of the Basket resource.
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
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\EmbeddedResources\BasketItem;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class BasketTest extends BaseUnitTest
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $basket = new Basket();
        $this->assertEquals(0, $basket->getAmountTotal());
        $this->assertEquals(0, $basket->getAmountTotalDiscount());
        $this->assertEquals('EUR', $basket->getCurrencyCode());
        $this->assertEquals('', $basket->getNote());
        $this->assertEquals('', $basket->getOrderId());
        $this->assertIsEmptyArray($basket->getBasketItems());
        $this->assertNull($basket->getBasketItemByIndex(1));

        $basket->setAmountTotal(1234);
        $basket->setAmountTotalDiscount(3456);
        $basket->setCurrencyCode('USD');
        $basket->setNote('This is something I have to remember!');
        $basket->setOrderId('myOrderId');
        $this->assertEquals(1234, $basket->getAmountTotal());
        $this->assertEquals(3456, $basket->getAmountTotalDiscount());
        $this->assertEquals('USD', $basket->getCurrencyCode());
        $this->assertEquals('This is something I have to remember!', $basket->getNote());
        $this->assertEquals('myOrderId', $basket->getOrderId());

        $this->assertEquals(0, $basket->getItemCount());
        $basketItem1 = new BasketItem();
        $basket->addBasketItem($basketItem1);
        $this->assertEquals(1, $basket->getItemCount());
        $this->assertSame($basketItem1, $basket->getBasketItemByIndex(0));

        $basketItem2 = new BasketItem();
        $basket->addBasketItem($basketItem2);
        $this->assertEquals(2, $basket->getItemCount());
        $this->assertNotSame($basketItem2, $basket->getBasketItemByIndex(0));
        $this->assertSame($basketItem2, $basket->getBasketItemByIndex(1));

        $this->assertArraySubset([$basketItem1, $basketItem2], $basket->getBasketItems());

        $basket->setBasketItems([]);
        $this->assertEquals(0, $basket->getItemCount());
        $this->assertIsEmptyArray($basket->getBasketItems());
        $this->assertNull($basket->getBasketItemByIndex(0));
        $this->assertNull($basket->getBasketItemByIndex(1));
    }

    /**
     * Verify expose will call expose on all attached BasketItems.
     *
     * @test
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws \ReflectionException
     */
    public function exposeShouldCallExposeOnAllAttachedBasketItems()
    {
        $basketItemMock = $this->getMockBuilder(BasketItem::class)->setMethods(['expose'])->getMock();
        $basketItemMock->expects($this->once())->method('expose')->willReturn('resultItem1');
        $basketItemMock2 = $this->getMockBuilder(BasketItem::class)->setMethods(['expose'])->getMock();
        $basketItemMock2->expects($this->once())->method('expose')->willReturn('resultItem2');

        $basket = (new Basket())->setBasketItems([$basketItemMock, $basketItemMock2]);

        $basketItemsExposed = $basket->expose()['basketItems'];
        self::assertContains('resultItem1', $basketItemsExposed);
        self::assertContains('resultItem2', $basketItemsExposed);
    }

    /**
     * Verify handleResponse will create basket items for each basketitem in response.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function handleResponseShouldCreateBasketItemObjectsForAllBasketItemsInResponse()
    {
        $response                = new \stdClass();
        $response->basketItems   = [];
        $basketItem1                = new \stdClass();
        $basketItem2                = new \stdClass();
        $response->basketItems[] = $basketItem1;
        $response->basketItems[] = $basketItem2;

        $basket =  new Basket();
        $this->assertEquals(0, $basket->getItemCount());
        $basket->handleResponse($response);
        $this->assertEquals(2, $basket->getItemCount());
        $basket->handleResponse($response);
        $this->assertEquals(2, $basket->getItemCount());
    }
}
