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
     * Sets whether this SEPA direct debit type is registered for recurring (mandate-based) collection.
     *
     * The intent is captured once at type registration: `true` yields a recurring (RCUR) mandate,
     * `false` (the default) a one-off (OOFF) mandate. The PMP derives the mandate type from this flag;
     * there is no recurrence data on the SEPA charge.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setRecurring(bool $active): self
    {
        $this->recurring = $active;
        return $this;
    }

    /**
     * Always exposes the recurring flag (default false) so the SEPA direct debit type registration
     * carries the mandate intent.
     *
     * {@inheritDoc}
     */
    public function expose()
    {
        $exposed = (array) parent::expose();
        $exposed['recurring'] = $this->recurring;
        ksort($exposed);

        return $exposed;
    }
}
