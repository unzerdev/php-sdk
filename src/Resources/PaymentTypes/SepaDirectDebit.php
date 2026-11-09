<?php
/**
 * This represents the SEPA direct debit payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Resources\Recurring;
use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Traits\CanPayout;

class SepaDirectDebit extends BasePaymentType
{
    use CanDirectCharge;
    use CanPayout;

    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /** @var bool|null $recurring Mandate-based recurring flag, accepted on type registration. */
    protected ?bool $recurring = null;

    /**
     * @param string|null $iban
     */
    public function __construct(?string $iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     *
     * @return $this
     */
    public function setIban(?string $iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @param string|null $bic
     *
     * @return $this
     */
    public function setBic(?string $bic): self
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHolder(): ?string
    {
        return $this->holder;
    }

    /**
     * @param string|null $holder
     *
     * @return $this
     */
    public function setHolder(?string $holder): self
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * @return bool Whether the payment type is registered for recurring (mandate-based) collection.
     */
    public function isRecurring(): bool
    {
        return $this->recurring ?? false;
    }

    /**
     * Registers the payment type for recurring (mandate-based) collection. Set this before creating the type;
     * it is sent in the `POST /v1/types/sepa-direct-debit` request body. A fresh instance that never sets it
     * leaves the flag out of the request entirely.
     *
     * @param bool $recurring
     *
     * @return $this
     */
    public function setRecurring(bool $recurring): self
    {
        $this->recurring = $recurring;
        return $this;
    }

    /**
     * Activates recurring payment for the payment type.
     *
     * @param string     $returnUrl      The URL to which the customer gets redirected in case of a 3ds transaction.
     * @param null|mixed $recurrenceType Recurrence type used for recurring payment.
     *
     * @return Recurring
     *
     * @throws \UnzerSDK\Exceptions\UnzerApiException An UnzerApiException is thrown if there is an error returned
     *                                                on API-request.
     *
     * @deprecated since 1.3.0.0 Please set the recurrence type in your Charge/Authorize object using
     *             `setRecurrenceType` before performing the transaction request.
     */
    public function activateRecurring($returnUrl, $recurrenceType = null): Recurring
    {
        return $this->getUnzerObject()->activateRecurringPayment($this, $returnUrl, $recurrenceType);
    }
}
