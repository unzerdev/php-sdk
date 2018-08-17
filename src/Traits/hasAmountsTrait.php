<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

namespace heidelpay\NmgPhpSdk\Traits;

trait hasAmountsTrait
{
    private $total = 0.0;
    private $charged = 0.0;
    private $canceled = 0.0;
    private $remaining = 0.0;

    /** @var string $currency */
    private $currency = '';

    //<editor-fold desc="Getters/Setters">
    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return self
     */
    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return float
     */
    public function getCharged(): float
    {
        return $this->charged;
    }

    /**
     * @param float $charged
     * @return self
     */
    public function setCharged(float $charged): self
    {
        $this->charged = $charged;
        return $this;
    }

    /**
     * @return float
     */
    public function getCanceled(): float
    {
        return $this->canceled;
    }

    /**
     * @param float $canceled
     * @return self
     */
    public function setCanceled(float $canceled): self
    {
        $this->canceled = $canceled;
        return $this;
    }

    /**
     * @return float
     */
    public function getRemaining(): float
    {
        return $this->remaining;
    }

    /**
     * @param float $remaining
     * @return self
     */
    public function setRemaining(float $remaining): self
    {
        $this->remaining = $remaining;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return hasAmountsTrait
     */
    public function setCurrency(string $currency): hasAmountsTrait
    {
        $this->currency = $currency;
        return $this;
    }
    //</editor-fold>
}
