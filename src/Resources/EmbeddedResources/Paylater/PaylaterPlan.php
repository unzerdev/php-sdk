<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paylater;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Resources\AbstractUnzerResource;
use stdClass;

/**
 * Resource representing a single plan item returned by the generalized paylater /plans endpoint.
 *
 * The same PAPI schema backs all three paylater types (installment, invoice, direct debit), but each
 * type only populates a subset of the optional fields: invoice and direct debit omit the interest-rate
 * fields and send rates without a date, while installment carries the full set. Every getter is therefore
 * nullable so a field a given type does not return reads back as null instead of throwing.
 *
 *  @link  https://docs.unzer.com/
 *
 */
class PaylaterPlan extends AbstractUnzerResource
{
    /** @var int|null $numberOfRates */
    private $numberOfRates;

    /** @var string|null $totalAmount */
    private $totalAmount;

    /** @var string|null $interestAmount */
    private $interestAmount;

    /** @var string|null $minimumInstallmentFee */
    private $minimumInstallmentFee;

    /** @var float|null $nominalInterestRate */
    private $nominalInterestRate;

    /** @var float|null $effectiveInterestRate */
    private $effectiveInterestRate;

    /** @var string|null $secciUrl */
    private $secciUrl;

    /** @var InstallmentRate[]|null $installmentRates */
    private $installmentRates;

    /**
     * @return string|null
     */
    public function getSecciUrl(): ?string
    {
        return $this->secciUrl;
    }

    /**
     * @param string|null $secciUrl
     *
     * @return PaylaterPlan
     */
    public function setSecciUrl(?string $secciUrl): PaylaterPlan
    {
        $this->secciUrl = $secciUrl;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumberOfRates(): ?int
    {
        return $this->numberOfRates;
    }

    /**
     * @param int|null $numberOfRates
     *
     * @return PaylaterPlan
     */
    public function setNumberOfRates(?int $numberOfRates): PaylaterPlan
    {
        $this->numberOfRates = $numberOfRates;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    /**
     * @param string|null $totalAmount
     *
     * @return PaylaterPlan
     */
    public function setTotalAmount(?string $totalAmount): PaylaterPlan
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInterestAmount(): ?string
    {
        return $this->interestAmount;
    }

    /**
     * @param string|null $interestAmount
     *
     * @return PaylaterPlan
     */
    public function setInterestAmount(?string $interestAmount): PaylaterPlan
    {
        $this->interestAmount = $interestAmount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMinimumInstallmentFee(): ?string
    {
        return $this->minimumInstallmentFee;
    }

    /**
     * @param string|null $minimumInstallmentFee
     *
     * @return PaylaterPlan
     */
    public function setMinimumInstallmentFee(?string $minimumInstallmentFee): PaylaterPlan
    {
        $this->minimumInstallmentFee = $minimumInstallmentFee;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getNominalInterestRate(): ?float
    {
        return $this->nominalInterestRate;
    }

    /**
     * @param float|null $nominalInterestRate
     *
     * @return PaylaterPlan
     */
    public function setNominalInterestRate(?float $nominalInterestRate): PaylaterPlan
    {
        $this->nominalInterestRate = $nominalInterestRate;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getEffectiveInterestRate(): ?float
    {
        return $this->effectiveInterestRate;
    }

    /**
     * @param float|null $effectiveInterestRate
     *
     * @return PaylaterPlan
     */
    public function setEffectiveInterestRate(?float $effectiveInterestRate): PaylaterPlan
    {
        $this->effectiveInterestRate = $effectiveInterestRate;
        return $this;
    }

    /**
     * @return InstallmentRate[]|null
     */
    public function getInstallmentRates(): ?array
    {
        return $this->installmentRates;
    }

    /**
     * @param InstallmentRate[]|null $installmentRates
     *
     * @return PaylaterPlan
     */
    protected function setInstallmentRates(?array $installmentRates): PaylaterPlan
    {
        $this->installmentRates = $installmentRates;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, string $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        if (isset($response->installmentRates)) {
            $rates = [];
            foreach ($response->installmentRates as $rate) {
                $rates[] = new InstallmentRate($rate->date ?? null, $rate->rate ?? null);
            }
            $this->setInstallmentRates($rates);
        }
    }
}
