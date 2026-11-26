<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paylater;

use UnzerSDK\Constants\CustomerTypes;
use UnzerSDK\Resources\AbstractUnzerResource;

/**
 * This class contains the query properties which are used to fetch paylater plans
 * (e.g. Paylater Installment, Paylater Invoice, Paylater Direct Debit).
 *
 * @link  https://docs.unzer.com/
 *
 */
class PaylaterPlansQuery extends AbstractUnzerResource
{
    protected float $amount;
    protected string $currency;
    protected string $country;
    protected string $customerType;

    /**
     * @param float  $amount
     * @param string $currency
     * @param string $country
     * @param string $customerType
     */
    public function __construct(float $amount, string $currency, string $country, string $customerType = CustomerTypes::B2C)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->country = $country;
        $this->customerType = $customerType;
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
     * @return PaylaterPlansQuery
     */
    public function setAmount(float $amount): PaylaterPlansQuery
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
     * @return PaylaterPlansQuery
     */
    public function setCurrency(string $currency): PaylaterPlansQuery
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return PaylaterPlansQuery
     */
    public function setCountry(string $country): PaylaterPlansQuery
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerType(): string
    {
        return $this->customerType;
    }

    /**
     * @param string $customerType
     *
     * @return PaylaterPlansQuery
     */
    public function setCustomerType(string $customerType): PaylaterPlansQuery
    {
        $this->customerType = $customerType;
        return $this;
    }
}
