<?php

namespace UnzerSDK\Resources\TransactionTypes;

use RuntimeException;
use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Traits\HasAccountInformation;
use UnzerSDK\Traits\HasCancellations;
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
    use HasCancellations;
    use HasRecurrenceType;
    use HasAccountInformation;
    use HasDescriptor;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string $paymentReference */
    protected $paymentReference;

    /** @var bool $card3ds */
    protected $card3ds;

    /** @var Authorization[] $authorizations */
    private $authorizations = [];

    /** @var Charge[] $charges */
    private $charges = [];

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
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     *
     * @return self
     */
    public function setAmount(?float $amount): self
    {
        $this->amount = $amount !== null ? round($amount, 4) : null;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCancelledAmount(): ?float
    {
        $amount = 0.0;
        foreach ($this->getCancellations() as $cancellation) {
            /** @var Cancellation $cancellation */
            if ($cancellation->isSuccess()) {
                $amount += $cancellation->getAmount();
            }
        }

        return $amount;
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
     * @return Authorization[]
     */
    public function getAuthorizations(): array
    {
        return $this->authorizations;
    }

    /**
     * @param Authorization $authorization
     *
     * @return Sca
     */
    public function addAuthorization(Authorization $authorization): Sca
    {
        $this->authorizations[] = $authorization;
        return $this;
    }

    /**
     * @return Charge[]
     */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /**
     * @param Charge $charge
     *
     * @return Sca
     */
    public function addCharge(Charge $charge): Sca
    {
        $this->charges[] = $charge;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(string $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return 'sca';
    }

    /**
     * Charge SCA transaction.
     *
     * @param float|null $amount
     *
     * @return Charge
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function charge(?float $amount = null): Charge
    {
        $payment = $this->getPayment();
        if (!$payment instanceof Payment) {
            throw new RuntimeException('Payment object is missing. Try fetching the object first!');
        }
        return $this->getUnzerObject()->chargeScaTransaction($payment, $this->getId(), $amount);
    }

    /**
     * Authorize SCA transaction.
     *
     * @param float|null $amount
     *
     * @return Authorization
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function authorize(?float $amount = null): Authorization
    {
        $payment = $this->getPayment();
        if (!$payment instanceof Payment) {
            throw new RuntimeException('Payment object is missing. Try fetching the object first!');
        }
        return $this->getUnzerObject()->authorizeScaTransaction($payment, $this->getId(), $amount);
    }
}
