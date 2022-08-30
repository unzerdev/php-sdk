<?php
/**
 * This trait allows a transaction type to have additional transaction Data.
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
 * @link     https://docs.unzer.com/
 *
 * @package  UnzerSDK\Traits
 */

namespace UnzerSDK\Traits;

use stdClass;
use UnzerSDK\Constants\AdditionalTransactionDataKeys;
use UnzerSDK\Resources\EmbeddedResources\RiskData;
use UnzerSDK\Resources\EmbeddedResources\ShippingData;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

trait HasAdditionalTransactionData
{
    /** @var stdClass $additionalTransactionData */
    protected $additionalTransactionData;

    /** Return additionalTransactionData as a Std class object.
     *
     * @return stdClass|null
     */
    public function getAdditionalTransactionData(): ?stdClass
    {
        return $this->additionalTransactionData;
    }

    /**
     * @param stdClass $additionalTransactionData
     *
     * @return AbstractTransactionType
     */
    public function setAdditionalTransactionData(stdClass $additionalTransactionData): self
    {
        $this->additionalTransactionData = $additionalTransactionData;
        return $this;
    }

    /** Add a single element to the additionalTransactionData object.
     *
     * @param mixed $value
     * @param mixed $name
     *
     * @return AbstractTransactionType
     */
    public function addAdditionalTransactionData($name, $value): self
    {
        if (null === $this->additionalTransactionData) {
            $this->additionalTransactionData = new stdClass();
        }
        $this->additionalTransactionData->$name = $value;
        return $this;
    }

    /**
     * Sets the shipping value inside the additional transaction Data array.
     *
     * @param ShippingData|null $shippingData
     *
     * @return $this
     */
    public function setShipping(?ShippingData $shippingData): self
    {
        $this->addAdditionalTransactionData('shipping', $shippingData);
        return $this;
    }

    /**
     * Gets the shipping value from the additional transaction Data array.
     *
     * @return ShippingData|null Returns null if shipping is empty or does not contain a ShippingObject.
     */
    public function getShipping(): ?ShippingData
    {
        $shipping = $this->getAdditionalTransactionData()->shipping ?? null;
        return $shipping instanceof ShippingData ? $shipping : null;
    }

    /**
     * Sets the riskData value inside the additional transaction Data array.
     *
     * @param RiskData|null $riskData
     *
     * @return $this
     */
    public function setRiskData(?RiskData $riskData): self
    {
        $this->addAdditionalTransactionData('riskData', $riskData);
        return $this;
    }

    /**
     * Gets the riskData value from the additional transaction Data array.
     *
     * @return RiskData|null
     */
    public function getRiskData(): ?RiskData
    {
        $riskData = $this->getAdditionalTransactionData()->riskData ?? null;
        return $riskData instanceof RiskData ? $riskData : null;
    }

    /**
     * Sets the privacyPolicyUrl value inside the additional transaction Data array.
     *
     * @param string|null $privacyPolicyUrl
     *
     * @return $this
     */
    public function setPrivacyPolicyUrl(?string $privacyPolicyUrl): self
    {
        $this->addAdditionalTransactionData(AdditionalTransactionDataKeys::PRIVACY_POLICY_URL, $privacyPolicyUrl);
        return $this;
    }

    /**
     * Gets the privacyPolicyUrl value from the additional transaction Data array.
     *
     * @return string|null
     */
    public function getPrivacyPolicyUrl(): ?string
    {
        $propertyKey = AdditionalTransactionDataKeys::PRIVACY_POLICY_URL;
        return $this->getAdditionalTransactionData()->$propertyKey ?? null;
    }

    /**
     * Sets the termsAndConditionUrl value inside the additional transaction Data array.
     *
     * @param string|null $termsAndConditionUrl
     *
     * @return $this
     */
    public function setTermsAndConditionUrl(?string $termsAndConditionUrl): self
    {
        $this->addAdditionalTransactionData(AdditionalTransactionDataKeys::TERMS_AND_CONDITION_URL, $termsAndConditionUrl);
        return $this;
    }

    /**
     * Gets the termsAndConditionUrl value from the additional transaction Data array.
     *
     * @return string|null
     */
    public function getTermsAndConditionUrl(): ?string
    {
        $propertyKey = AdditionalTransactionDataKeys::TERMS_AND_CONDITION_URL;
        return $this->getAdditionalTransactionData()->$propertyKey ?? null;
    }
}
