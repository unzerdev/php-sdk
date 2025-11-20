<?php

namespace UnzerSDK\Resources\TransactionTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Traits\HasAccountInformation;
use UnzerSDK\Traits\HasDescriptor;
use UnzerSDK\Traits\HasRecurrenceType;

/**
 * This represents the SCA (Strong Customer Authentication) transaction.
 *
 * @link  https://docs.unzer.com/
 *
 */
class Sca extends AbstractTransactionType
{
    use HasRecurrenceType;
    use HasAccountInformation;
    use HasDescriptor;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string $paymentReference */
    protected $paymentReference;

    /** @var bool $card3ds */
    protected $card3ds;

    /**
     * Sca constructor.
     *
     * @param float|null $amount
     * @param string|null $currency
     * @param string|null $returnUrl
     */
    public function __construct(?float $amount = null, ?string $currency = null, ?string $returnUrl = null)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setReturnUrl($returnUrl);
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string|null $currency
     *
     * @return self
     */
    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * @param string|null $returnUrl
     *
     * @return self
     */
    public function setReturnUrl(?string $returnUrl): self
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    /**
     * @param string|null $referenceText
     *
     * @return Sca
     */
    public function setPaymentReference(?string $referenceText): Sca
    {
        $this->paymentReference = $referenceText;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isCard3ds(): ?bool
    {
        return $this->card3ds;
    }

    /**
     * @param bool|null $card3ds
     *
     * @return Sca
     */
    public function setCard3ds(?bool $card3ds): Sca
    {
        $this->card3ds = $card3ds;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(string $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return 'sca';
    }
}
