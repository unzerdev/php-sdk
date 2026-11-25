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
class InstallmentPlan extends AbstractUnzerResource
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
     * @return InstallmentPlan
     */
    public function setSecciUrl(?string $secciUrl): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setNumberOfRates(?int $numberOfRates): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setTotalAmount(?string $totalAmount): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setInterestAmount(?string $interestAmount): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setMinimumInstallmentFee(?string $minimumInstallmentFee): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setNominalInterestRate(?float $nominalInterestRate): InstallmentPlan
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
     * @return InstallmentPlan
     */
    public function setEffectiveInterestRate(?float $effectiveInterestRate): InstallmentPlan
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
     * @return InstallmentPlan
     */
    protected function setInstallmentRates(?array $installmentRates): InstallmentPlan
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
