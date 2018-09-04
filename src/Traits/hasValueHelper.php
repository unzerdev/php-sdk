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

use heidelpay\NmgPhpSdk\Constants\Calculation;

trait hasValueHelper
{
    /**
     * Returns true if amount1 is greater than or equal to amount2.
     *
     * @param float $amount1
     * @param float $amount2
     * @return bool
     */
    private function amountIsGreaterThanOrEqual($amount1, $amount2): bool
    {
        $diff = $amount1 - $amount2;
        return $diff > 0.0 || $this->equalsZero($diff);
    }

    /**
     * Returns true if the given amount is smaller than EPSILON.
     *
     * @param float $amount
     * @return bool
     */
    private function equalsZero(float $amount): bool
    {
        return (abs($amount) < Calculation::EPSILON);
    }

}
