<?php
/**
 * todo Description
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
 * todo
 *
 * @package  heidelpayPHP/
 */
namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Traits\CanAuthorize;
use stdClass;

class InstallmentPlan extends BasePaymentType
{
    use CanAuthorize;

    /** @var string $orderDate */
    protected $orderDate;

    /** @var int $numberOfRates */
    protected $numberOfRates;

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

    /** @var InstalmentPlans $plans */
    protected $plans;

    /** @var stdClass[] */
    private $rates;

    /**
     * @param int    $numberOfRates
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
        $numberOfRates = null,
        $dayOfPurchase = null,
        $totalPurchaseAmount = null,
        $totalInterestAmount = null,
        $totalAmount = null,
        $effectiveInterestRate = null,
        $nominalInterestRate = null,
        $feeFirstRate = null,
        $feePerRate = null,
        $monthlyRate = null,
        $lastRate = null
    ) {
        $this->numberOfRates         = $numberOfRates;
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
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    /**
     * @param string|null $orderDate
     *
     * @return $this
     */
    public function setOrderDate($orderDate): self
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfRates(): int
    {
        return $this->numberOfRates;
    }

    /**
     * @param int $numberOfRates
     *
     * @return $this
     */
    public function setNumberOfRates(int $numberOfRates): self
    {
        $this->numberOfRates = $numberOfRates;
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
     * @return $this
     */
    public function setDayOfPurchase(string $dayOfPurchase): self
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
     * @return $this
     */
    public function setTotalPurchaseAmount(float $totalPurchaseAmount): self
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
     * @return $this
     */
    public function setTotalInterestAmount(float $totalInterestAmount): self
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
     * @return $this
     */
    public function setTotalAmount(float $totalAmount): self
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
     * @return $this
     */
    public function setEffectiveInterestRate(float $effectiveInterestRate): self
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
     * @return $this
     */
    public function setNominalInterestRate(float $nominalInterestRate): self
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
     * @return $this
     */
    public function setFeeFirstRate(float $feeFirstRate): self
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
     * @return $this
     */
    public function setFeePerRate(float $feePerRate): self
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
     * @return $this
     */
    public function setMonthlyRate(float $monthlyRate): self
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
     * @return $this
     */
    public function setLastRate(float $lastRate): self
    {
        $this->lastRate = $lastRate;
        return $this;
    }

    /**
     * @return InstalmentPlans
     */
    public function getPlans(): InstalmentPlans
    {
        return $this->plans;
    }

    /**
     * @param InstalmentPlans $plans
     *
     * @return $this
     */
    public function setPlans(InstalmentPlans $plans): self
    {
        $this->plans = $plans;
        return $this;
    }

    /**
     * @return stdClass[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     * @param stdClass[] $rates
     * @return InstallmentPlan
     */
    public function setRates(array $rates): InstallmentPlan
    {
        $this->rates = $rates;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    public function getTransactionParams(): array
    {
        return [
            'effectiveInterestRate' => $this->getEffectiveInterestRate()
        ];
    }

    //</editor-fold>
}
