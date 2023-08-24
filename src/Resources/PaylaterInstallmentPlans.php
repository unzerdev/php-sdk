<?php
/**
 * Resource used to fetch instalment plans for Installment Secured payment method specified as parent resource.
 * Please use Unzer methods to fetch the list of instalment plans
 * (e.g. Unzer::fetchInstallmentPlans(...)).
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
use stdClass;
use UnzerSDK\Resources\EmbeddedResources\Paylater\InstallmentPlansQuery;
use UnzerSDK\Resources\EmbeddedResources\Paylater\InstallmentPlan;
use UnzerSDK\Traits\HasStates;

class PaylaterInstallmentPlans extends AbstractUnzerResource
{
    use HasStates;

    /** @var float */
    private $amount;

    /** @var string */
    private $currency;

    /** @var string */
    private $country;

    /** @var string */
    private $customerType;

    /** @var InstallmentPlan[] $plans */
    private $plans = [];

    /** @var InstallmentPlansQuery Query parameter used to fetch available installment plans. */
    private $queryParameter;

    /**
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return PaylaterInstallmentPlans
     */
    public function setAmount(float $amount): PaylaterInstallmentPlans
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return InstalmentPlans
     */
    public function setCurrency(string $currency): PaylaterInstallmentPlans
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return PaylaterInstallmentPlans
     */
    public function setCountry(string $country): PaylaterInstallmentPlans
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerType(): ?string
    {
        return $this->customerType;
    }

    /**
     * @param string $customerType
     *
     * @return PaylaterInstallmentPlans
     */
    public function setCustomerType(string $customerType): PaylaterInstallmentPlans
    {
        $this->customerType = $customerType;
        return $this;
    }

    /**
     * @return InstallmentPlan[]
     */
    public function getPlans(): array
    {
        return $this->plans;
    }

    /**
     * @param stdClass[] $plans
     *
     * @return InstalmentPlans
     */
    protected function setPlans(array $plans): PaylaterInstallmentPlans
    {
        $this->plans = $plans;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResourcePath(string $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return 'plans' . $this->getQueryString();
    }

    /**
     * Returns the query string for this resource.
     *
     * @return string
     */
    protected function getQueryString(): string
    {
        return '?' . http_build_query($this->getQueryArray());
    }

    /**
     * Returns the parameter array containing the values for the query string.
     *
     * @return array
     */
    protected function getQueryArray(): array
    {
        $parameters = [];
        if ($this->queryParameter === null) {
            return $parameters;
        }

        return $this->getQueryParameter()->expose();
    }

    /**
     * @return InstallmentPlansQuery
     */
    public function getQueryParameter(): InstallmentPlansQuery
    {
        return $this->queryParameter;
    }

    /**
     * @param InstallmentPlansQuery $queryParameter
     *
     * @return PaylaterInstallmentPlans
     */
    public function setQueryParameter(InstallmentPlansQuery $queryParameter): PaylaterInstallmentPlans
    {
        $this->queryParameter = $queryParameter;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, string $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        if (isset($response->plans)) {
            $plans = [];
            foreach ($response->plans as $plan) {
                $instalment = new InstallmentPlan();
                $instalment->handleResponse($plan);
                $plans[] = $instalment;
            }
            $this->setPlans($plans);
        }
    }
}
