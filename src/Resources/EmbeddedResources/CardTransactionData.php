<?php

/*
 * Represents `card` object of `additionalTransactionData'.
 *
 *  Copyright (C) 2023 - today Unzer E-Com GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @link  https://docs.unzer.com/
 *
 *  @package  UnzerSDK
 *
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

class CardTransactionData extends AbstractUnzerResource
{
    /** @var string|null $recurrenceType */
    protected $recurrenceType;
    /** @var string|null $exemptionType */
    protected $exemptionType;
    /** @var string|null $liability */
    private $liability;

    /**
     * @return string|null
     */
    public function getRecurrenceType(): ?string
    {
        return $this->recurrenceType;
    }

    /**
     * @param string|null $recurrenceType
     *
     * @return CardTransactionData
     */
    public function setRecurrenceType(?string $recurrenceType): CardTransactionData
    {
        $this->recurrenceType = $recurrenceType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLiability(): ?string
    {
        return $this->liability;
    }

    /**
     * @param string|null $liability
     *
     * @return CardTransactionData
     */
    public function setLiability(?string $liability): CardTransactionData
    {
        $this->liability = $liability;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExemptionType(): ?string
    {
        return $this->exemptionType;
    }

    /**
     * @param string|null $exemptionType
     *
     * @return CardTransactionData
     */
    public function setExemptionType(?string $exemptionType): CardTransactionData
    {
        $this->exemptionType = $exemptionType;
        return $this;
    }
}
