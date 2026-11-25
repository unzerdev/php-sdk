<?php

namespace UnzerSDK\Resources;

use UnzerSDK\Adapter\HttpAdapterInterface;
use stdClass;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;
use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlan;
use UnzerSDK\Traits\HasStates;

/**
 * Resource used to fetch the available plans for a paylater payment method specified as parent resource
 * (e.g. Paylater Installment, Paylater Invoice, Paylater Direct Debit).
 * Please use Unzer methods to fetch the list of plans (e.g. Unzer::fetchPaylaterPlans(...)).
 *
 * @link  https://docs.unzer.com/
 *
 */
class PaylaterPlans extends AbstractUnzerResource
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

    /** @var PaylaterPlan[] $plans */
    private $plans = [];

    /** @var PaylaterPlansQuery Query parameter used to fetch available plans. */
    private $queryParameter;

    /**
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     *
     * @return PaylaterPlans
     */
    public function setAmount(?float $amount): PaylaterPlans
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
     * @param string|null $currency
     *
     * @return PaylaterPlans
     */
    public function setCurrency(?string $currency): PaylaterPlans
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
     * @param string|null $country
     *
     * @return PaylaterPlans
     */
    public function setCountry(?string $country): PaylaterPlans
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
     * @param string|null $customerType
     *
     * @return PaylaterPlans
     */
    public function setCustomerType(?string $customerType): PaylaterPlans
    {
        $this->customerType = $customerType;
        return $this;
    }

    /**
     * @return PaylaterPlan[]
     */
    public function getPlans(): array
    {
        return $this->plans;
    }

    /**
     * @param stdClass[]|null $plans
     *
     * @return PaylaterPlans
     */
    protected function setPlans(?array $plans): PaylaterPlans
    {
        $this->plans = $plans ?? [];
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
     * @return PaylaterPlansQuery
     */
    public function getQueryParameter(): PaylaterPlansQuery
    {
        return $this->queryParameter;
    }

    /**
     * @param PaylaterPlansQuery $queryParameter
     *
     * @return PaylaterPlans
     */
    public function setQueryParameter(PaylaterPlansQuery $queryParameter): PaylaterPlans
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
                $paylaterPlan = new PaylaterPlan();
                $paylaterPlan->handleResponse($plan);
                $plans[] = $paylaterPlan;
            }
            $this->setPlans($plans);
        }
    }
}
