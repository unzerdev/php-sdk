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

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    protected function getResourcePath(): string
    {
        return 'paypage/charge/';
    }

    //</editor-fold>
}
