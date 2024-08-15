<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paypage;

use UnzerSDK\Resources\AbstractUnzerResource;

class PaymentMethodsConfigs extends AbstractUnzerResource
{
    protected ?PaymentMethodConfig $default = null;
    protected ?string $preselectedMethod = null;

    /** @var PaymentMethodConfig[] */
    protected ?array $methodConfigs = null;

    public function expose()
    {
        $exposeArray = parent::expose();
        $paymentMethodConfigs = $this->getMethodConfigs();

        if (empty($paymentMethodConfigs)) {
            return $exposeArray;
        }

        foreach ($paymentMethodConfigs as $type => $methodConfig) {
            $exposeArray[$type] = $methodConfig->expose();
        }
        unset($exposeArray['methodConfigs']);

        return $exposeArray;
    }


    public function getDefault(): ?PaymentMethodConfig
    {
        return $this->default;
    }

    public function setDefault(?PaymentMethodConfig $default): PaymentMethodsConfigs
    {
        $this->default = $default;
        return $this;
    }

    public function getPreselectedMethod(): ?string
    {
        return $this->preselectedMethod;
    }

    public function setPreselectedMethod(?string $preselectedMethod): PaymentMethodsConfigs
    {
        $this->preselectedMethod = $preselectedMethod;
        return $this;
    }

    public function getMethodConfigs(): ?array
    {
        return $this->methodConfigs;
    }

    public function setMethodConfigs(?array $methodConfigs): PaymentMethodsConfigs
    {
        $this->methodConfigs = $methodConfigs;
        return $this;
    }
}