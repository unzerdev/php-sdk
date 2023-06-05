<?php
/**
 * Resource representing the installment plan for Paylater Installment.
 *
 * Copyright (C) 2023 - today Unzer E-Com GmbH
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
 * @package  UnzerSDK\Resources
 */

namespace UnzerSDK\Resources;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Resources\EmbeddedResources\PaylaterInstallmentRate;
use stdClass;

class PaylaterInstallmentPlan extends AbstractUnzerResource
{
    /** @var int $numberOfRates */
    private $numberOfRates;

    private $totalAmount;

    private $nominalInterestRate;

    /** @var float $effectiveInterestRate */
    private $effectiveInterestRate;

    /** @var string $secciUrl */
    private $secciUrl;
    /** @var array */
    private $installmentRates;

    /**
     * @return string
     */
    public function getSecciUrl(): string
    {
        return $this->secciUrl;
    }

    /**
     * @param string $secciUrl
     * @return PaylaterInstallmentPlan
     */
    public function setSecciUrl(string $secciUrl): PaylaterInstallmentPlan
    {
        $this->secciUrl = $secciUrl;
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
     * @return PaylaterInstallmentPlan
     */
    public function setNumberOfRates(int $numberOfRates): PaylaterInstallmentPlan
    {
        $this->numberOfRates = $numberOfRates;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param mixed $totalAmount
     *
     * @return PaylaterInstallmentPlan
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNominalInterestRate()
    {
        return $this->nominalInterestRate;
    }

    /**
     * @param mixed $nominalInterestRate
     *
     * @return PaylaterInstallmentPlan
     */
    public function setNominalInterestRate($nominalInterestRate)
    {
        $this->nominalInterestRate = $nominalInterestRate;
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
     * @return PaylaterInstallmentPlan
     */
    public function setEffectiveInterestRate(float $effectiveInterestRate): PaylaterInstallmentPlan
    {
        $this->effectiveInterestRate = $effectiveInterestRate;
        return $this;
    }

    /**
     * @return PaylaterInstallmentRate[]|null
     */
    public function getInstallmentRates(): ?array
    {
        return $this->installmentRates;
    }

    /**
     * @param PaylaterInstallmentRate[] $installmentRates
     *
     * @return InstalmentPlan
     */
    protected function setInstallmentRates(array $installmentRates): PaylaterInstallmentPlan
    {
        $this->installmentRates = $installmentRates;
        return $this;
    }

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, string $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        if (isset($response->installmentRates)) {
            $rates = [];
            foreach ($response->installmentRates as $rate) {
                $rates[] = new PaylaterInstallmentRate($rate->date, $rate->rate);
            }
            $this->setInstallmentRates($rates);
        }
    }

    //</editor-fold>
}
