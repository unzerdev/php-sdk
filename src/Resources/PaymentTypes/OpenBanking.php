<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;

class OpenBanking extends BasePaymentType
{
    use CanAuthorize;
    use CanDirectCharge;

    /** @var string|null $ibanCountry */
    protected $ibanCountry;

    public function __construct(string $ibanCountry = null)
    {
        $this->ibanCountry = $ibanCountry;
    }

    /**
     * @return string|null
     */
    public function getIbanCountry(): ?string
    {
        return $this->ibanCountry;
    }

    /**
     * @param string|null $ibanCountry
     *
     * @return OpenBanking
     */
    public function setIbanCountry(string $ibanCountry): OpenBanking
    {
        $this->ibanCountry = $ibanCountry;
        return $this;
    }


}
