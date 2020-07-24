<?php
/**
 * This represents the Recurring resource.
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
 * @package  heidelpayPHP\Resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Traits\HasCustomerMessage;
use heidelpayPHP\Traits\HasDate;
use heidelpayPHP\Traits\HasStates;
use heidelpayPHP\Traits\HasUniqueAndShortId;

class Recurring extends AbstractHeidelpayResource
{
    use HasStates;
    use HasUniqueAndShortId;
    use HasCustomerMessage;
    use HasDate;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string|null $redirectUrl */
    protected $redirectUrl;

    /** @var string $paymentTypeId */
    private $paymentTypeId;

    /**
     * @param string $paymentType
     * @param string $returnUrl
     */
    public function __construct(string $paymentType, string $returnUrl)
    {
        $this->returnUrl     = $returnUrl;
        $this->paymentTypeId = $paymentType;
    }

    //<editor-fold desc="Getters/Setters">

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
     * @return Recurring
     */
    public function setReturnUrl(string $returnUrl): Recurring
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentTypeId(): string
    {
        return $this->paymentTypeId;
    }

    /**
     * @param string $paymentTypeId
     *
     * @return Recurring
     */
    public function setPaymentTypeId(string $paymentTypeId): Recurring
    {
        $this->paymentTypeId = $paymentTypeId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string|null $redirectUrl
     *
     * @return Recurring
     */
    protected function setRedirectUrl($redirectUrl): Recurring
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        $parts = [
            'types',
            $this->paymentTypeId,
            parent::getResourcePath()
        ];

        return implode('/', $parts);
    }

    //</editor-fold>
}
