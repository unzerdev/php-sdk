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
        $basket = new Basket('', 0, '', []);
        $this->assertEquals(0, $basket->getAmountTotal());
        $this->assertEquals(0, $basket->getAmountTotalDiscount());
        $this->assertEquals('', $basket->getCurrencyCode());
        $this->assertEquals('', $basket->getNote());
        $this->assertEquals('', $basket->getOrderId());
        $this->assertIsEmptyArray($basket->getBasketItems());
        $this->assertNull($basket->getBasketItemByIndex(1));

        $basket->setAmountTotal(1234);
        $basket->setAmountTotalDiscount(3456);
        $basket->setCurrencyCode('EUR');
        $basket->setNote('This is something I have to remember!');
        $basket->setOrderId('myOrderId');
        $this->assertEquals(1234, $basket->getAmountTotal());
        $this->assertEquals(3456, $basket->getAmountTotalDiscount());
        $this->assertEquals('EUR', $basket->getCurrencyCode());
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
}
