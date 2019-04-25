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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/payment_types
 */
namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Traits\CanAuthorize;

class HirePurchaseDirectDebit extends BasePaymentType
{
    use CanAuthorize;

    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $accountHolder */
    protected $accountHolder;

    /** @var string $orderDate */
    protected $orderDate;

    /** @var int $amountOfRates */
    protected $amountOfRates;

    /** @var string $dayOfPurchase */
    protected $dayOfPurchase;

    /** @var float $totalPurchaseAmount*/
    protected $totalPurchaseAmount;

    /** @var float $totalInterestAmount */
    protected $totalInterestAmount;

    /** @var float $totalAmount */
    protected $totalAmount;

    /** @var float $effectiveInterestRate */
    protected $effectiveInterestRate;

    /** @var float $nominalInterestRate */
    protected $nominalInterestRate;

    /** @var float $feeFirstRate */
    protected $feeFirstRate;

    /** @var float $feePerRate */
    protected $feePerRate;

    /** @var float $monthlyRate */
    protected $monthlyRate;

    /** @var float $lastRate */
    protected $lastRate;

    /**
     * @param string $iban
     * @param string $bic
     * @param string $accountHolder
     * @param int    $amountOfRates
     * @param string $dayOfPurchase
     * @param float  $totalPurchaseAmount
     * @param float  $totalInterestAmount
     * @param float  $totalAmount
     * @param float  $effectiveInterestRate
     * @param float  $nominalInterestRate
     * @param float  $feeFirstRate
     * @param float  $feePerRate
     * @param float  $monthlyRate
     * @param float  $lastRate
     */
    public function __construct(
        $iban,
        $bic,
        $accountHolder,
        $amountOfRates,
        $dayOfPurchase,
        $totalPurchaseAmount,
        $totalInterestAmount,
        $totalAmount,
        $effectiveInterestRate,
        $nominalInterestRate,
        $feeFirstRate,
        $feePerRate,
        $monthlyRate,
        $lastRate
    ) {
        $this->iban                  = $iban;
        $this->bic                   = $bic;
        $this->accountHolder         = $accountHolder;
        $this->amountOfRates         = $amountOfRates;
        $this->dayOfPurchase         = $dayOfPurchase;
        $this->totalPurchaseAmount   = $totalPurchaseAmount;
        $this->totalInterestAmount   = $totalInterestAmount;
        $this->totalAmount           = $totalAmount;
        $this->effectiveInterestRate = $effectiveInterestRate;
        $this->nominalInterestRate   = $nominalInterestRate;
        $this->feeFirstRate          = $feeFirstRate;
        $this->feePerRate            = $feePerRate;
        $this->monthlyRate           = $monthlyRate;
        $this->lastRate              = $lastRate;
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
     * @param string $iban
     *
     * @return $this
     */
    public function setIban(string $iban): self
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
     * @param string $bic
     *
     * @return $this
     */
    public function setBic(string $bic): self
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
     * @param string $accountHolder
     *
     * @return $this
     */
    public function setAccountHolder(string $accountHolder): self
    {
        $this->accountHolder = $accountHolder;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    /**
     * @param string|null $orderDate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setOrderDate($orderDate): HirePurchaseDirectDebit
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmountOfRates(): int
    {
        return $this->amountOfRates;
    }

    /**
     * @param int $amountOfRates
     *
     * @return HirePurchaseDirectDebit
     */
    public function setAmountOfRates(int $amountOfRates): HirePurchaseDirectDebit
    {
        $this->amountOfRates = $amountOfRates;
        return $this;
    }

    /**
     * @return string
     */
    public function getDayOfPurchase(): string
    {
        return $this->dayOfPurchase;
    }

    /**
     * @param string $dayOfPurchase
     *
     * @return HirePurchaseDirectDebit
     */
    public function setDayOfPurchase(string $dayOfPurchase): HirePurchaseDirectDebit
    {
        $this->dayOfPurchase = $dayOfPurchase;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalPurchaseAmount(): float
    {
        return $this->totalPurchaseAmount;
    }

    /**
     * @param float $totalPurchaseAmount
     *
     * @return HirePurchaseDirectDebit
     */
    public function setTotalPurchaseAmount(float $totalPurchaseAmount): HirePurchaseDirectDebit
    {
        $this->totalPurchaseAmount = $totalPurchaseAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInterestAmount(): float
    {
        return $this->totalInterestAmount;
    }

    /**
     * @param float $totalInterestAmount
     *
     * @return HirePurchaseDirectDebit
     */
    public function setTotalInterestAmount(float $totalInterestAmount): HirePurchaseDirectDebit
    {
        $this->totalInterestAmount = $totalInterestAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     *
     * @return HirePurchaseDirectDebit
     */
    public function setTotalAmount(float $totalAmount): HirePurchaseDirectDebit
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @return float
     */
    public function getEffectiveInterestRate(): float
    {
        return $this->effectiveInterestRate;
    }

    /**
     * @param float $effectiveInterestRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setEffectiveInterestRate(float $effectiveInterestRate): HirePurchaseDirectDebit
    {
        $this->effectiveInterestRate = $effectiveInterestRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getNominalInterestRate(): float
    {
        return $this->nominalInterestRate;
    }

    /**
     * @param float $nominalInterestRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setNominalInterestRate(float $nominalInterestRate): HirePurchaseDirectDebit
    {
        $this->nominalInterestRate = $nominalInterestRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getFeeFirstRate(): float
    {
        return $this->feeFirstRate;
    }

    /**
     * @param float $feeFirstRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setFeeFirstRate(float $feeFirstRate): HirePurchaseDirectDebit
    {
        $this->feeFirstRate = $feeFirstRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getFeePerRate(): float
    {
        return $this->feePerRate;
    }

    /**
     * @param float $feePerRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setFeePerRate(float $feePerRate): HirePurchaseDirectDebit
    {
        $this->feePerRate = $feePerRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getMonthlyRate(): float
    {
        return $this->monthlyRate;
    }

    /**
     * @param float $monthlyRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setMonthlyRate(float $monthlyRate): HirePurchaseDirectDebit
    {
        $this->monthlyRate = $monthlyRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getLastRate(): float
    {
        return $this->lastRate;
    }

    /**
     * @param float $lastRate
     *
     * @return HirePurchaseDirectDebit
     */
    public function setLastRate(float $lastRate): HirePurchaseDirectDebit
    {
        $this->lastRate = $lastRate;
        return $this;
    }

    //</editor-fold>
}
