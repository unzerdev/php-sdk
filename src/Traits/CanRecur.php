<?php
/**
 * This trait allows a payment type to activate recurring payments.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Traits;

trait CanRecur
{
    /** @var bool $recurring */
    private $recurring = false;

    /**
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    /**
     * @param bool $active
     *
     * @return self
     */
    protected function setRecurring(bool $active): self
    {
        $this->recurring = $active;
        return $this;
    }
}
