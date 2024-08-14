<?php

namespace UnzerSDK\Resources\V2;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Apis\PaypageAPIConfig;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Resources\AbstractUnzerResource;

class Paypage extends AbstractUnzerResource
{
    const URI = '/merchant/paypage';
    protected float $amount;
    protected string $currency;

    /**
     * @var string $mode "charge" or "authorize"
     */
    protected string $mode;

    /** @var string $redirectUrl */
    private $redirectUrl;
    private $paypageId;

    /**
     * @param $amount
     * @param $currency
     * @param $mode
     */
    public function __construct($amount, $currency, $mode = TransactionTypes::CHARGE)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->mode = $mode;
    }


    /**
     * @return mixed
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return Paypage
     */
    public function setAmount($amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getApiConfig(): string
    {
        return PaypageAPIConfig::class;
    }

    public function getUri(bool $appendId = true, string $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return self::URI;
    }

    public function getApiVersion(): string
    {
        return 'v2';
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): Paypage
    {
        $this->currency = $currency;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): Paypage
    {
        $this->mode = $mode;
        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): Paypage
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaypageId(): ?string
    {
        return $this->paypageId;
    }

    /**
     * @param mixed $paypageId
     * @return Paypage
     */
    public function setPaypageId($paypageId): self
    {
        $this->paypageId = $paypageId;
        return $this;
    }
}