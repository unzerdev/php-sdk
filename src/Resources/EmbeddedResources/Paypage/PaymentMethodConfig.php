<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paypage;

use UnzerSDK\Resources\AbstractUnzerResource;

class PaymentMethodConfig extends AbstractUnzerResource
{
    protected ?bool $enabled = null;
    protected ?int $order = null;

    // type specific configs.
    protected ?string $name = null;
    protected ?string $checkoutType = null; // paypal only.

    protected ?bool $credentialOnFile = null; // card only.
    protected ?string $exemption = null; // card only.

    /**
     * @param bool|null $enabled
     * @param int|null $order
     */
    public function __construct(?bool $enabled = null, ?int $order = null)
    {
        $this->enabled = $enabled;
        $this->order = $order;
    }


    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): PaymentMethodConfig
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): PaymentMethodConfig
    {
        $this->order = $order;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): PaymentMethodConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getCheckoutType(): ?string
    {
        return $this->checkoutType;
    }

    public function setCheckoutType(?string $checkoutType): PaymentMethodConfig
    {
        $this->checkoutType = $checkoutType;
        return $this;
    }

    public function getCredentialOnFile(): ?bool
    {
        return $this->credentialOnFile;
    }

    public function setCredentialOnFile(?bool $credentialOnFile): PaymentMethodConfig
    {
        $this->credentialOnFile = $credentialOnFile;
        return $this;
    }

    public function getExemption(): ?string
    {
        return $this->exemption;
    }

    public function setExemption(?string $exemption): PaymentMethodConfig
    {
        $this->exemption = $exemption;
        return $this;
    }
}