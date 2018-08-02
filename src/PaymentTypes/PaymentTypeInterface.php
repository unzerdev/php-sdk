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
namespace heidelpay\NmgPhpSdk\PaymentTypes;

interface PaymentTypeInterface
{
    /**
     * @param float $amount
     * @param string $currency
     * @return $this
     */
    public function charge($amount, $currency);

    /**
     * @param float $amount
     * @param string $currency
     * @return $this
     */
    public function authorize($amount, $currency);
}
