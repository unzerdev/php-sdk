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

interface AmountsInterface
{
    /**
     * Return the total amount.
     *
     * @return float
     */
    public function getTotal(): float;

    /**
     * Return the remaining amount.
     *
     * @return float
     */
    public function getRemaining(): float;

    /**
     * Return the charged amount.
     *
     * @return float
     */
    public function getCharged(): float;

    /**
     * Return the canceled amount.
     *
     * @return float
     */
    public function getCanceled(): float;
}
