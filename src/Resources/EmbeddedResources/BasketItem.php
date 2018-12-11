<?php
/**
 * This trait adds amount properties to a class.
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
 * @package  heidelpayPHP/resources/embedded_resources
 */
namespace heidelpayPHP\Resources\EmbeddedResources;

use heidelpayPHP\Resources\AbstractHeidelpayResource;

class BasketItem extends AbstractHeidelpayResource
{
    /** @var string $basketItemReferenceId */
    protected $basketItemReferenceId = '';

    /** @var int $quantity */
    protected $quantity = 0;

    /** @var int $vat */
    protected $vat = 0;

    /** @var int $amountDiscount */
    protected $amountDiscount = 0;

    /** @var int $amountGross */
    protected $amountGross = 0;

    /** @var int $amountGross */
    protected $amountVat = 0;

    /** @var int $amountPerUnit */
    protected $amountPerUnit = 0;

    /** @var int $amountNet */
    protected $amountNet = 0;

    /** @var string $unit */
    protected $unit = '';

    /** @var string $title */
    protected $title = '';

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getBasketItemReferenceId(): string
    {
        return $this->basketItemReferenceId;
    }

    /**
     * @param string $basketItemReferenceId
     *
     * @return BasketItem
     */
    public function setBasketItemReferenceId(string $basketItemReferenceId): BasketItem
    {
        $this->basketItemReferenceId = $basketItemReferenceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return BasketItem
     */
    public function setQuantity(int $quantity): BasketItem
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getVat(): int
    {
        return $this->vat;
    }

    /**
     * @param int $vat
     *
     * @return BasketItem
     */
    public function setVat(int $vat): BasketItem
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountDiscount(): int
    {
        return $this->amountDiscount;
    }

    /**
     * @param int $amountDiscount
     *
     * @return BasketItem
     */
    public function setAmountDiscount(int $amountDiscount): BasketItem
    {
        $this->amountDiscount = $amountDiscount;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountGross(): int
    {
        return $this->amountGross;
    }

    /**
     * @param int $amountGross
     *
     * @return BasketItem
     */
    public function setAmountGross(int $amountGross): BasketItem
    {
        $this->amountGross = $amountGross;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountVat(): int
    {
        return $this->amountVat;
    }

    /**
     * @param int $amountVat
     *
     * @return BasketItem
     */
    public function setAmountVat(int $amountVat): BasketItem
    {
        $this->amountVat = $amountVat;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountPerUnit(): int
    {
        return $this->amountPerUnit;
    }

    /**
     * @param int $amountPerUnit
     *
     * @return BasketItem
     */
    public function setAmountPerUnit(int $amountPerUnit): BasketItem
    {
        $this->amountPerUnit = $amountPerUnit;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountNet(): int
    {
        return $this->amountNet;
    }

    /**
     * @param int $amountNet
     *
     * @return BasketItem
     */
    public function setAmountNet(int $amountNet): BasketItem
    {
        $this->amountNet = $amountNet;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return BasketItem
     */
    public function setUnit(string $unit): BasketItem
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return BasketItem
     */
    public function setTitle(string $title): BasketItem
    {
        $this->title = $title;
        return $this;
    }

    //</editor-fold>
}
