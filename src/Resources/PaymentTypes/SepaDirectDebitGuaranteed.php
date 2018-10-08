<?php
/**
 * This represents the SEPA direct debit guaranteed payment type.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/payment_types
 */
namespace heidelpay\MgwPhpSdk\Resources\PaymentTypes;

class SepaDirectDebitGuaranteed extends BasePaymentType
{
    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /**
     * @param string $iban
     */
    public function __construct(string $iban)
    {
        $this->setChargeable(true);

        $this->iban = $iban;

        parent::__construct();
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getIban(): string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     * @return SepaDirectDebitGuaranteed
     */
    public function setIban(string $iban): SepaDirectDebitGuaranteed
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     * @return SepaDirectDebitGuaranteed
     */
    public function setBic(string $bic): SepaDirectDebitGuaranteed
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * @return string
     */
    public function getHolder(): string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     * @return SepaDirectDebitGuaranteed
     */
    public function setHolder(string $holder): SepaDirectDebitGuaranteed
    {
        $this->holder = $holder;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/sepa-direct-debit-guaranteed';
    }
    //</editor-fold>
}
