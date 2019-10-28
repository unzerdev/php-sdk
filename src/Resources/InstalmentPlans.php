<?php
/**
 * This class contains the instalment plans for payment method hire purchase (flexipay rate).
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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Adapter\HttpAdapterInterface;
use stdClass;

class InstalmentPlans extends AbstractHeidelpayResource
{
    /** @var float */
    private $amount;

    /** @var string */
    private $currency;

    /** @var float */
    private $effectiveInterest;

    /** var stdClass[] $plans */
    private $plans = [];

    /**
     * InstalmentPlans constructor.
     *
     * @param float  $amount
     * @param string $currency
     * @param float  $effectiveInterest
     */
    public function __construct(float $amount, string $currency, float $effectiveInterest)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->effectiveInterest = $effectiveInterest;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return InstalmentPlans
     */
    public function setAmount(float $amount): InstalmentPlans
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return InstalmentPlans
     */
    public function setCurrency(string $currency): InstalmentPlans
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return float
     */
    public function getEffectiveInterest(): float
    {
        return $this->effectiveInterest;
    }

    /**
     * @param float $effectiveInterest
     *
     * @return InstalmentPlans
     */
    public function setEffectiveInterest(float $effectiveInterest): InstalmentPlans
    {
        $this->effectiveInterest = $effectiveInterest;
        return $this;
    }

    /**
     * @return stdClass[]
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
    protected function setPlans(array $plans): InstalmentPlans
    {
        $this->plans = $plans;
        return $this;
    }

    //<editor-fold desc="Overridable methods">

    /**
     * Returns the parameter array containing the values for the query string.
     *
     * @return array
     */
    protected function getQueryArray(): array
    {
        $parameters = [];
        $parameters['amount'] = $this->getAmount();
        $parameters['currency'] = $this->getCurrency();
        $parameters['effectiveInterest'] = $this->getEffectiveInterest();
        return $parameters;
    }

    /**
     * Returns the query string for this resource.
     *
     * @return string
     */
    protected function getQueryString(): string
    {
        $getParameterArray = $this->getQueryArray();
        foreach ($getParameterArray as $key=> $parameter) {
            $getParameterArray[$key] = $key . '=' . $parameter;
        }

        return '?' . implode('&', $getParameterArray);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourcePath(): string
    {
        return 'plans' . $this->getQueryString();
    }

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        parent::handleResponse($response, $method);
        if (isset($response->entity)) {
            $this->plans = $response->entity;
        }
    }

    //</editor-fold>
}
