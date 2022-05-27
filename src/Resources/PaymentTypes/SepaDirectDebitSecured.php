<?php
/**
 * This represents the SEPA direct debit secured payment type.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectChargeWithCustomer;
use UnzerSDK\Traits\CanPayoutWithCustomer;
use UnzerSDK\Traits\CanRecur;

class SepaDirectDebitSecured extends BasePaymentType
{
    use CanDirectChargeWithCustomer;
    use CanPayoutWithCustomer;
    use CanRecur;

    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /**
     * @param string $iban
     */
    public function __construct($iban)
    {
        $this->iban = $iban;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return $this
     */
    public function setIban($iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     *
     * @return $this
     */
    public function setBic($bic): self
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHolder(): ?string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     *
     * @return $this
     */
    public function setHolder($holder): self
    {
        $this->holder = $holder;
        return $this;
    }

    //</editor-fold>
}
