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

namespace heidelpay\NmgPhpSdk;

class Risk extends AbstractHeidelpayResource
{
    /** @var bool $guestCheckout */
    private $guestCheckout = false;

    /** @var string $customerSince */
    private $customerSince = '';

    /** @var int $orderCount */
    private $orderCount = 0;

    public function getUri(): string
    {
        return '/risks';
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return bool
     */
    public function isGuestCheckout(): bool
    {
        return $this->guestCheckout;
    }

    /**
     * @param bool $guestCheckout
     * @return Risk
     */
    public function setGuestCheckout(bool $guestCheckout): Risk
    {
        $this->guestCheckout = $guestCheckout;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerSince(): string
    {
        return $this->customerSince;
    }

    /**
     * @param string $customerSince
     * @return Risk
     */
    public function setCustomerSince(string $customerSince): Risk
    {
        $this->customerSince = $customerSince;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderCount(): int
    {
        return $this->orderCount;
    }

    /**
     * @param int $orderCount
     * @return Risk
     */
    public function setOrderCount(int $orderCount): Risk
    {
        $this->orderCount = $orderCount;
        return $this;
    }
    //</editor-fold>
}
