<?php

namespace UnzerSDK\Resources\V2;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Apis\PaypageAPIConfig;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\EmbeddedResources\Paypage\PaymentMethodsConfigs;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Resources;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Style;
use UnzerSDK\Resources\EmbeddedResources\Paypage\Urls;
use UnzerSDK\Resources\EmbeddedResources\RiskData;

class Paypage extends AbstractUnzerResource
{
    public const URI = '/merchant/paypage';

    /** @var string $mode "charge" or "authorize" */
    protected string $mode;
    protected float $amount;
    protected string $currency;

    protected ?string $type = null;
    protected ?string $recurrenceType = null;
    protected ?string $logoImage = null;
    protected ?string $shopName = null;
    protected ?string $orderId = null;
    protected ?string $invoiceId = null;
    protected ?string $paymentReference = null;

    protected ?Urls $urls = null;
    protected ?Style $style = null;
    protected ?Resources $resources;
    protected $paymentMethodsConfigs;
    protected ?RiskData $risk = null;

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
     *
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
     *
     * @return Paypage
     */
    public function setPaypageId($paypageId): self
    {
        $this->paypageId = $paypageId;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Paypage
    {
        $this->type = $type;
        return $this;
    }

    public function getRecurrenceType(): ?string
    {
        return $this->recurrenceType;
    }

    public function setRecurrenceType(?string $recurrenceType): Paypage
    {
        $this->recurrenceType = $recurrenceType;
        return $this;
    }

    public function getLogoImage(): ?string
    {
        return $this->logoImage;
    }

    public function setLogoImage(?string $logoImage): Paypage
    {
        $this->logoImage = $logoImage;
        return $this;
    }

    public function getShopName(): ?string
    {
        return $this->shopName;
    }

    public function setShopName(?string $shopName): Paypage
    {
        $this->shopName = $shopName;
        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): Paypage
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    public function setInvoiceId(?string $invoiceId): Paypage
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): Paypage
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    public function getUrls(): ?Urls
    {
        return $this->urls;
    }

    public function setUrls(Urls $urls): Paypage
    {
        $this->urls = $urls;
        return $this;
    }

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function setStyle(Style $style): Paypage
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResources()
    {
        return $this->resources;
    }

    public function setResources(Resources $resources): Paypage
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethodsConfigs(): ?array
    {
        return $this->paymentMethodsConfigs;
    }

    public function setPaymentMethodsConfigs(PaymentMethodsConfigs $paymentMethodsConfigs)
    {
        $this->paymentMethodsConfigs = $paymentMethodsConfigs;
        return $this;
    }

    public function getRisk(): ?RiskData
    {
        return $this->risk;
    }

    public function setRisk(RiskData $risk): Paypage
    {
        $this->risk = $risk;
        return $this;
    }
}
