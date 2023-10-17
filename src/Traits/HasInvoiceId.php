<?php
/**
 * This trait adds the invoiceId property to a class.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\Traits
 */

namespace UnzerSDK\Traits;

trait HasInvoiceId
{
    /** @var string $invoiceId */
    protected $invoiceId;

    /**
     * @return string|null
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * @param string|null $invoiceId
     *
     * @return self
     */
    public function setInvoiceId(?string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }
}
