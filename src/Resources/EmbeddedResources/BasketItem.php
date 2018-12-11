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
    /** @var int $position */
    private $position = 0;

    /** @var string $channel */
    private $channel = '';

    /** @var string $usage */
    private $usage = '';

    /** @var string $basketItemReferenceId */
    private $basketItemReferenceId = '';

    /** @var string $unit */
    private $unit = '';

    /** @var int $quantity */
    private $quantity = 0;

    /** @var int $amountDiscount */
    private $amountDiscount = 0;

    /** @var int $vat */
    private $vat = 0;

    /** @var int $amountPerUnit */
    private $amountPerUnit = 0;

    /** @var int $amountGross */
    private $amountGross = 0;

    /** @var string $articleId */
    private $articleId = '';

    /** @var string $type */
    private $type = '';

    /** @var string $title */
    private $title = '';

    /** @var string $description */
    private $description = '';

    /** @var string $imageUrl */
    private $imageUrl = '';

    //<editor-fold desc="Getters/Setters">

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return BasketItem
     */
    public function setPosition(int $position): BasketItem
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return BasketItem
     */
    public function setChannel(string $channel): BasketItem
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsage(): string
    {
        return $this->usage;
    }

    /**
     * @param string $usage
     *
     * @return BasketItem
     */
    public function setUsage(string $usage): BasketItem
    {
        $this->usage = $usage;
        return $this;
    }

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
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     *
     * @return BasketItem
     */
    public function setArticleId(string $articleId): BasketItem
    {
        $this->articleId = $articleId;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return BasketItem
     */
    public function setType(string $type): BasketItem
    {
        $this->type = $type;
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

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return BasketItem
     */
    public function setDescription(string $description): BasketItem
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return BasketItem
     */
    public function setImageUrl(string $imageUrl): BasketItem
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    //</editor-fold>
}
