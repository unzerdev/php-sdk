<?php
/**
 * This represents the ApplePay payment type.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Traits\CanPayout;
use UnzerSDK\Traits\CanRecur;
use UnzerSDK\Traits\HasGeoLocation;

class Applepay extends BasePaymentType
{
    use CanDirectCharge;
    use CanAuthorize;
    use CanPayout;
    use CanRecur;
    use HasGeoLocation;


    /** @var string|null $applicationExpirationDate */
    protected $applicationExpirationDate;

    /** @var string|null $applicationPrimaryAccountNumber */
    protected $applicationPrimaryAccountNumber;

    /** @var string|null $currencyCode */
    protected $currencyCode;

    /** @var string|null $data */
    protected $data;

    /** @var string|null $method */
    protected $method;

    /** @var string|null $signature */
    protected $signature;

    /** @var float $transactionAmount */
    protected $transactionAmount = 0.0;

    /** @var string|null $version */
    protected $version;

    /**
     * ApplePay constructor.
     */
    public function __construct()
    {
    }

    //<editor-fold desc="Getters/Setters"

    /**
     * @return string|null
     */
    public function getApplicationExpirationDate(): ?string
    {
        return $this->applicationExpirationDate;
    }

    /**
     * @return string|null
     */
    public function getApplicationPrimaryAccountNumber(): ?string
    {
        return $this->applicationPrimaryAccountNumber;
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    /**
     * @return string|null
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getSignature(): ?string
    {
        return $this->signature;
    }

    /**
     * @return float
     */
    public function getTransactionAmount(): float
    {
        return $this->transactionAmount;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $applicationExpirationDate
     *
     * @return $this
     */
    public function setApplicationExpirationDate(?string $applicationExpirationDate): Applepay
    {
        $this->applicationExpirationDate = $applicationExpirationDate;
        return $this;
    }

    /**
     * @param string|null $applicationPrimaryAccountNumber
     *
     * @return $this
     */
    public function setApplicationPrimaryAccountNumber(?string $applicationPrimaryAccountNumber): Applepay
    {
        $this->applicationPrimaryAccountNumber = $applicationPrimaryAccountNumber;
        return $this;
    }

    /**
     * @param string|null $currencyCode
     *
     * @return $this
     */
    public function setCurrencyCode(?string $currencyCode): Applepay
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @param string|null $data
     *
     * @return $this
     */
    public function setData(?string $data): Applepay
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string|null $method
     *
     * @return $this
     */
    public function setMethod(?string $method): Applepay
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param string|null $signature
     *
     * @return $this
     */
    public function setSignature(?string $signature): Applepay
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @param float $transactionAmount
     *
     * @return $this
     */
    public function setTransactionAmount(float $transactionAmount): Applepay
    {
        $this->transactionAmount = $transactionAmount;
        return $this;
    }

    /**
     * @param string|null $version
     *
     * @return $this
     */
    public function setVersion(?string $version): Applepay
    {
        $this->version = $version;
        return $this;
    }

    //</editor-fold>
}
