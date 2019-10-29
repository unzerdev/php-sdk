<?php
/**
 * This represents the Hire Purchase payment type.
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
use stdClass;

class HirePurchaseDirectDebit extends InstalmentPlan
{
    use CanAuthorize;

    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $accountHolder */
    protected $accountHolder;

    /**
     * @param string $iban
     * @param string $accountHolder
     * @param string $bic
     */
    public function __construct($iban, $accountHolder, $bic = null)
    {
        parent::__construct();

        $this->iban          = $iban;
        $this->bic           = $bic;
        $this->accountHolder = $accountHolder;
    }

    /**
     * Updates the plan of this object with the information from the given instalment plan.
     *
     * @param InstalmentPlan|stdClass $plan
     *
     * @return $this
     */
    public function selectInstalmentPlan($plan): self
    {
        $data = $plan instanceof InstalmentPlan ? (object)$plan->expose() : $plan;
        $this->handleResponse($data);
        return $this;
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
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
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string|null $bic
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
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string|null $accountHolder
     *
     * @return $this
     */
    public function setAccountHolder($accountHolder): self
    {
        $this->accountHolder = $accountHolder;
        return $this;
    }

    //</editor-fold>
}
