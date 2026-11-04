<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the embedded BasketItem resource.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Resources\EmbeddedResources;

use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\test\BasePaymentTest;

class BasketItemTest extends BasePaymentTest
{
    /**
     * Verify setter and getter functionalities.
     *
     * @test
     */
    public function settersAndGettersShouldWork(): void
    {
        $basketItem = new BasketItem();
        $this->assertEquals(1, $basketItem->getQuantity());
        $this->assertEquals(0, $basketItem->getAmountPerUnitGross());
        $this->assertEquals(0, $basketItem->getAmountDiscountPerUnitGross());
        $this->assertEquals(0, $basketItem->getVat());
        $this->assertEquals('', $basketItem->getBasketItemReferenceId());
        $this->assertEquals('', $basketItem->getUnit());
        $this->assertEquals('', $basketItem->getTitle());
        $this->assertEquals('', $basketItem->getSubTitle());
        $this->assertNull($basketItem->getImageUrl());

        $basketItem->setQuantity(2);
        $basketItem->setVat(6543);
        $basketItem->setAmountPerUnitGross(5432);
        $basketItem->setAmountDiscountPerUnitGross(4321);
        $basketItem->setBasketItemReferenceId('myRefId');
        $basketItem->setUnit('myUnit');
        $basketItem->setTitle('myTitle');
        $basketItem->setSubTitle('mySubTitle');
        $basketItem->setImageUrl('https://my.image.url');
        $basketItem->setType('myType');

        $this->assertEquals(2, $basketItem->getQuantity());
        $this->assertEquals(6543, $basketItem->getVat());
        $this->assertEquals(5432, $basketItem->getAmountPerUnitGross());
        $this->assertEquals(4321, $basketItem->getAmountDiscountPerUnitGross());
        $this->assertEquals('myRefId', $basketItem->getBasketItemReferenceId());
        $this->assertEquals('myUnit', $basketItem->getUnit());
        $this->assertEquals('myTitle', $basketItem->getTitle());
        $this->assertEquals('mySubTitle', $basketItem->getSubTitle());
        $this->assertEquals('myType', $basketItem->getType());
        $this->assertEquals('https://my.image.url', $basketItem->getImageUrl());
    }
}
