<?php
/**
 * Installment rate class for PaylaterInstallment payment types.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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

class PaylaterInstallmentRate
{
    /** @var string|null $date */
    protected $date;

    /** @var string|null $rate Amount of rate.*/
    protected $rate;

    /**
     * @param string|null $date
     * @param string|null $rate
     */
    public function __construct(?string $date, ?string $rate)
    {
        $this->date = $date;
        $this->rate = $rate;
    }

    /**
     * @return string|null
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param string|null $date
     *
     * @return PaylaterInstallmentRate
     */
    public function setDate(?string $date): PaylaterInstallmentRate
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRate(): ?string
    {
        return $this->rate;
    }

    /**
     * @param string|null $rate
     *
     * @return PaylaterInstallmentRate
     */
    public function setRate(?string $rate): PaylaterInstallmentRate
    {
        $this->rate = $rate;
        return $this;
    }
}
