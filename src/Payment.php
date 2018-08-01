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
namespace heidelpay\PhpSdk;

class Payment
{
    private $paymentType;

    /**
     * Payment constructor.
     * @param $paymentType
     */
    public function __construct($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param mixed $paymentType
     * @return Payment
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
        return $this;
    }
    //</editor-fold>
}
