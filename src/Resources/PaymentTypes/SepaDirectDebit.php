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
     * Registers the payment type for recurring (mandate-based) collection.
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
     * {@inheritDoc}
     *
     * Adds the `recurring` flag to the request body. Unlike the other CanRecur consumers (where it stays a
     * read-only response flag), sepa-direct-debit accepts `recurring` on type registration, so it is exposed
     * here — but only once it has a value, keeping a fresh, never-set instance byte-for-byte as before.
     */
    public function expose()
    {
        $exposeArray = parent::expose();

        if ($this->recurring !== null) {
            if (!is_array($exposeArray)) {
                $exposeArray = [];
            }
            $exposeArray['recurring'] = $this->recurring;
            ksort($exposeArray);
        }

        return $exposeArray;
    }
}
