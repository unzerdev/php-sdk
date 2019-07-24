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

use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Traits\CanAuthorize;
use heidelpayPHP\Traits\CanDirectCharge;
use heidelpayPHP\Traits\HasInvoiceId;
use heidelpayPHP\Traits\HasOrderId;
use stdClass;

class Paypage extends BasePaymentType
{
    use CanDirectCharge;
    use CanAuthorize;
    use HasInvoiceId;
    use HasOrderId;

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

    /** @var string $termsAndConditionUrl */
    protected $termsAndConditionUrl;

    /** @var string $privacyPolicyUrl */
    protected $privacyPolicyUrl;

    /** @var string $imprintUrl */
    protected $imprintUrl;

    /** @var string $helpUrl */
    protected $helpUrl;

    /** @var string $contactUrl */
    protected $contactUrl;

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
     *
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
     *
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
     *
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
     *
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
     *
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
    public function getTermsAndConditionUrl(): string
    {
        return $this->termsAndConditionUrl;
    }

    /**
     * @param string $termsAndConditionUrl
     *
     * @return Paypage
     */
    public function setTermsAndConditionUrl(string $termsAndConditionUrl): Paypage
    {
        $this->termsAndConditionUrl = $termsAndConditionUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivacyPolicyUrl(): string
    {
        return $this->privacyPolicyUrl;
    }

    /**
     * @param string $privacyPolicyUrl
     *
     * @return Paypage
     */
    public function setPrivacyPolicyUrl(string $privacyPolicyUrl): Paypage
    {
        $this->privacyPolicyUrl = $privacyPolicyUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getImprintUrl(): string
    {
        return $this->imprintUrl;
    }

    /**
     * @param string $imprintUrl
     *
     * @return Paypage
     */
    public function setImprintUrl(string $imprintUrl): Paypage
    {
        $this->imprintUrl = $imprintUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getHelpUrl(): string
    {
        return $this->helpUrl;
    }

    /**
     * @param string $helpUrl
     *
     * @return Paypage
     */
    public function setHelpUrl(string $helpUrl): Paypage
    {
        $this->helpUrl = $helpUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactUrl(): string
    {
        return $this->contactUrl;
    }

    /**
     * @param string $contactUrl
     *
     * @return Paypage
     */
    public function setContactUrl(string $contactUrl): Paypage
    {
        $this->contactUrl = $contactUrl;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    /**
     * {@inheritDoc}
     * Change resource path.
     */
    protected function getResourcePath(): string
    {
        return 'paypage/charge/';
    }

    /**
     * {@inheritDoc}
     * Map external name of property to internal name of property.
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        if (isset($response->impressumUrl)) {
            $response->imprintUrl = $response->impressumUrl;
            unset($response->impressumUrl);
        }

        parent::handleResponse($response, $method);
    }

    /**
     * {@inheritDoc}
     * Map external name of property to internal name of property.
     */
    public function expose()
    {
        $exposeArray = parent::expose();
        if (isset($exposeArray['imprintUrl'])) {
            $exposeArray['impressumUrl'] = $exposeArray['imprintUrl'];
            unset($exposeArray['imprintUrl']);
        }

        return $exposeArray;
    }

    //</editor-fold>
}
