<?php
/**
 * This represents the basket resource.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Resources\EmbeddedResources\BasketItem;

class Basket extends AbstractHeidelpayResource
{
    /** @var int $amountTotalNet */
    private $amountTotalNet = 0;

    /** @var int $amountTotalVat */
    private $amountTotalVat = 0;

    /** @var int $amountTotalDiscount */
    private $amountTotalDiscount = 0;

    /** @var string $currencyCode */
    private $currencyCode = '';

    /** @var string $note */
    private $note = '';

    /** @var string $basketReferenceId */
    private $basketReferenceId = '';

    /** @var array $basketItems */
    private $basketItems = [];

    //<editor-fold desc="Getters/Setters">

    /**
     * @return int
     */
    public function getAmountTotalNet(): int
    {
        return $this->amountTotalNet;
    }

    /**
     * @param int $amountTotalNet
     *
     * @return Basket
     */
    public function setAmountTotalNet(int $amountTotalNet): Basket
    {
        $this->amountTotalNet = $amountTotalNet;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountTotalVat(): int
    {
        return $this->amountTotalVat;
    }

    /**
     * @param int $amountTotalVat
     *
     * @return Basket
     */
    public function setAmountTotalVat(int $amountTotalVat): Basket
    {
        $this->amountTotalVat = $amountTotalVat;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountTotalDiscount(): int
    {
        return $this->amountTotalDiscount;
    }

    /**
     * @param int $amountTotalDiscount
     *
     * @return Basket
     */
    public function setAmountTotalDiscount(int $amountTotalDiscount): Basket
    {
        $this->amountTotalDiscount = $amountTotalDiscount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return Basket
     */
    public function setCurrencyCode(string $currencyCode): Basket
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return count($this->basketItems);
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return Basket
     */
    public function setNote(string $note): Basket
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasketReferenceId(): string
    {
        return $this->basketReferenceId;
    }

    /**
     * @param string $basketReferenceId
     *
     * @return Basket
     */
    public function setBasketReferenceId(string $basketReferenceId): Basket
    {
        $this->basketReferenceId = $basketReferenceId;
        return $this;
    }

    /**
     * @return array
     */
    public function getBasketItems(): array
    {
        return $this->basketItems;
    }

    /**
     * @param array $basketItems
     *
     * @return Basket
     */
    public function setBasketItems(array $basketItems): Basket
    {
        $this->basketItems = $basketItems;
        return $this;
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return Basket
     */
    public function addBasketItem(BasketItem $basketItem): Basket
    {
        $this->basketItems[] = $basketItem;
        return $this;
    }

    /**
     * @param int $index
     *
     * @return BasketItem|null
     */
    public function getBasketItemByIndex($index)
    {
        return $this->basketItems[$index] ?? null;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'baskets';
    }
    
    //</editor-fold>
}
