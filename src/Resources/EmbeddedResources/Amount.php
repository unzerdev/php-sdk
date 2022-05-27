<?php
/**
 * This class contains the amount properties which are mainly used by the payment class.
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
 * @package  UnzerSDK\Resources\EmbeddedResources
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

class Amount extends AbstractUnzerResource
{
    private $total = 0.0;
    private $charged = 0.0;
    private $canceled = 0.0;
    private $remaining = 0.0;

    /** @var string $currency */
    private $currency;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     *
     * @return $this
     */
    protected function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return float
     */
    public function getCharged(): float
    {
        return $this->charged;
    }

    /**
     * @param float $charged
     *
     * @return $this
     */
    protected function setCharged(float $charged): self
    {
        $this->charged = $charged;
        return $this;
    }

    /**
     * @return float
     */
    public function getCanceled(): float
    {
        return $this->canceled;
    }

    /**
     * @param float $canceled
     *
     * @return self
     */
    protected function setCanceled(float $canceled): self
    {
        $this->canceled = $canceled;
        return $this;
    }

    /**
     * @return float
     */
    public function getRemaining(): float
    {
        return $this->remaining;
    }

    /**
     * @param float $remaining
     *
     * @return self
     */
    protected function setRemaining(float $remaining): self
    {
        $this->remaining = $remaining;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return self
     */
    protected function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    //</editor-fold>
}
