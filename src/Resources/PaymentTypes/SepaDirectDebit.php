<?php
/**
 * This represents the SEPA direct debit payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Traits\CanPayout;
use UnzerSDK\Traits\CanRecur;

class SepaDirectDebit extends BasePaymentType
{
    use CanDirectCharge;
    use CanPayout;
    use CanRecur;

    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /**
     * Explicit request intent for the mandate-based recurring flag. CanRecur::$recurring is private and
     * response-only (never serialized); this nullable shadow lets expose() emit `recurring` — including an
     * explicit false — only once set, leaving a fresh instance's request body unchanged.
     *
     * @var bool|null
     */
    private ?bool $recurringRequested = null;

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
     * Overrides CanRecur::isRecurring() to read the request-time intent. Returns false for a fresh
     * instance and reflects the value mapped from a fetched resource.
     *
     * @return bool Whether the payment type is registered for recurring (mandate-based) collection.
     */
    public function isRecurring(): bool
    {
        return $this->recurringRequested ?? false;
    }

    /**
     * Registers the payment type for recurring (mandate-based) collection. Set this before creating the type;
     * it is sent in the `POST /v1/types/sepa-direct-debit` request body. A fresh instance that never sets it
     * leaves the flag out of the request entirely.
     *
     * Widens CanRecur::setRecurring() to public; the trait's own setter is protected.
     *
     * @param bool $recurring
     *
     * @return $this
     */
    public function setRecurring(bool $recurring): self
    {
        $this->recurringRequested = $recurring;
        return $this;
    }

    /**
     * Appends the recurring flag — including an explicit false — once set, without turning the trait's
     * private, response-only property into a serialized one.
     *
     * {@inheritDoc}
     */
    public function expose()
    {
        $exposed = parent::expose();

        if ($this->recurringRequested !== null) {
            $exposed = (array) $exposed;
            $exposed['recurring'] = $this->recurringRequested;
            ksort($exposed);
        }

        return $exposed;
    }
}
