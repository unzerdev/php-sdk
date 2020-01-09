<?php
/**
 * Resource used to fetch instalment plans for hire purchase (FlexiPay Rate) payment method specified as parent resource.
 * Please use Heidelpay methods to fetch the list of instalment plans
 * (e.g. Heidelpay::fetchDirectDebitInstalmentPlans(...)).
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
 * @package  heidelpayPHP\Resources
 */
namespace heidelpayPHP\Resources;

use DateTime;
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

    /** @var string|null */
    private $orderDate;

    /**
     * InstalmentPlans constructor.
     *
     * @param float                $amount
     * @param string               $currency
     * @param float                $effectiveInterest
     * @param DateTime|string|null $orderDate
     */
    public function __construct(
        float $amount,
        string $currency,
        float $effectiveInterest,
        $orderDate = null
    ) {
        $this->amount            = $amount;
        $this->currency          = $currency;
        $this->effectiveInterest = $effectiveInterest;
        $this->setOrderDate($orderDate);
    }

    //<editor-fold desc="Getters / Setters">

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

    /**
     * @return string|null
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @param string|DateTime|null $orderDate
     *
     * @return InstalmentPlans
     */
    public function setOrderDate($orderDate): InstalmentPlans
    {
        $this->orderDate = $orderDate instanceof DateTime ? $orderDate->format('Y-m-d') : $orderDate;
        return $this;
    }

    //</editor-fold>

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
        if ($this->getOrderDate() !== null) {
            $parameters['orderDate'] = $this->getOrderDate();
        }
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
            $plans = [];
            foreach ($response->entity as $plan) {
                $instalment = new InstalmentPlan();
                $instalment->handleResponse($plan);
                $plans[] = $instalment;
            }
            $this->setPlans($plans);
        }
    }

    //</editor-fold>
}
