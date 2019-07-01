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
    protected $basketItemReferenceId;

    /** @var int $quantity */
    protected $quantity = 1;

    /** @var float $vat */
    protected $vat = 0.0;

    /** @var float $amountDiscount */
    protected $amountDiscount = 0.0;

    /** @var float $amountGross */
    protected $amountGross = 0.0;

    /** @var float $amountVat */
    protected $amountVat = 0.0;

    /** @var float $amountPerUnit */
    protected $amountPerUnit = 0.0;

    /** @var float $amountNet */
    protected $amountNet = 0.0;

    /** @var string $unit */
    protected $unit;

    /** @var string $title */
    protected $title = '';

    /**
     * BasketItem constructor.
     *
     * @param string $title
     * @param float  $amountNet
     * @param float  $amountPerUnit
     * @param int    $quantity
     */
    public function __construct(
        string $title = '',
        float $amountNet = 0.0,
        float $amountPerUnit = 0.0,
        int $quantity = 1
    ) {
        $this->title                 = $title;
        $this->amountNet             = $amountNet;
        $this->amountPerUnit         = $amountPerUnit;
        $this->quantity              = $quantity;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getBasketItemReferenceId()
    {
        return $this->basketItemReferenceId;
    }

    /**
     * @param string|null $basketItemReferenceId
     *
     * @return BasketItem
     */
    public function setBasketItemReferenceId($basketItemReferenceId): BasketItem
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
     * @return float
     */
    public function getVat(): float
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     *
     * @return BasketItem
     */
    public function setVat(float $vat): BasketItem
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountDiscount(): float
    {
        return $this->amountDiscount;
    }

    /**
     * @param float $amountDiscount
     *
     * @return BasketItem
     */
    public function setAmountDiscount(float $amountDiscount): BasketItem
    {
        $this->amountDiscount = $amountDiscount;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountGross(): float
    {
        return $this->amountGross;
    }

    /**
     * @param float $amountGross
     *
     * @return BasketItem
     */
    public function setAmountGross(float $amountGross): BasketItem
    {
        $this->amountGross = $amountGross;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountVat(): float
    {
        return $this->amountVat;
    }

    /**
     * @param float $amountVat
     *
     * @return BasketItem
     */
    public function setAmountVat(float $amountVat): BasketItem
    {
        $this->amountVat = $amountVat;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountPerUnit(): float
    {
        return $this->amountPerUnit;
    }

    /**
     * @param float $amountPerUnit
     *
     * @return BasketItem
     */
    public function setAmountPerUnit(float $amountPerUnit): BasketItem
    {
        $this->amountPerUnit = $amountPerUnit;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    /**
     * @param float $amountNet
     *
     * @return BasketItem
     */
    public function setAmountNet(float $amountNet): BasketItem
    {
        $this->amountNet = $amountNet;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string|null $unit
     *
     * @return BasketItem
     */
    public function setUnit($unit): BasketItem
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
