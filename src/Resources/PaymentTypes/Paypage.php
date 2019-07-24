<?php
/**
 * This is the implementation of the Pay Page which allows for displaying a page containing all
 * payment types of the merchant.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP/payment_types
 */
namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Traits\CanAuthorize;
use heidelpayPHP\Traits\CanDirectCharge;

class Paypage extends BasePaymentType
{
    use CanDirectCharge;
    use CanAuthorize;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency*/
    protected $currency;

    /** @var string $returnUrl*/
    protected $returnUrl;

    /** @var string $logoImage */
    protected $logoImage;

    /** @var string $fullPageImage */
    protected $fullPageImage;

    /** @var string $shopName */
    protected $shopName;

    /** @var string $shopDescription */
    protected $shopDescription;

    /** @var string $tagline */
    protected $tagline;

    /** @var string $orderId */
    protected $orderId;

    /** @var string $termsAndConditionUrl */
    protected $termsAndConditionUrl;

    /**
     * Paypage constructor.
     *
     * @param float  $amount
     * @param string $currency
     * @param string $returnUrl
     */
    public function __construct(float $amount, string $currency, string $returnUrl)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->returnUrl = $returnUrl;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Paypage
     */
    public function setAmount(float $amount): Paypage
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Paypage
     */
    public function setCurrency(string $currency): Paypage
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return Paypage
     */
    public function setReturnUrl(string $returnUrl): Paypage
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoImage(): string
    {
        return $this->logoImage;
    }

    /**
     * @param string $logoImage
     * @return Paypage
     */
    public function setLogoImage(string $logoImage): Paypage
    {
        $this->logoImage = $logoImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullPageImage(): string
    {
        return $this->fullPageImage;
    }

    /**
     * @param string $fullPageImage
     * @return Paypage
     */
    public function setFullPageImage(string $fullPageImage): Paypage
    {
        $this->fullPageImage = $fullPageImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return $this->shopName;
    }

    /**
     * @param string $shopName
     * @return Paypage
     */
    public function setShopName(string $shopName): Paypage
    {
        $this->shopName = $shopName;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopDescription(): string
    {
        return $this->shopDescription;
    }

    /**
     * @param string $shopDescription
     * @return Paypage
     */
    public function setShopDescription(string $shopDescription): Paypage
    {
        $this->shopDescription = $shopDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getTagline(): string
    {
        return $this->tagline;
    }

    /**
     * @param string $tagline
     * @return Paypage
     */
    public function setTagline(string $tagline): Paypage
    {
        $this->tagline = $tagline;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return Paypage
     */
    public function setOrderId(string $orderId): Paypage
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTermsAndConditionUrl(): string
    {
        return $this->termsAndConditionUrl;
    }

    /**
     * @param string $termsAndConditionUrl
     * @return Paypage
     */
    public function setTermsAndConditionUrl(string $termsAndConditionUrl): Paypage
    {
        $this->termsAndConditionUrl = $termsAndConditionUrl;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    protected function getResourcePath(): string
    {
        return 'paypage/charge/';
    }

    //</editor-fold>
}
