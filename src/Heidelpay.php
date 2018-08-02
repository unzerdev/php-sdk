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

use heidelpay\NmgPhpSdk\Constants\Mode;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;

class Heidelpay
{
    private $key;
    private $returnUrl;

    /** @var Payment $payment */
    private $payment;

    /** @var bool */
    private $sandboxMode = true;

    /**
     * Heidelpay constructor.
     *
     * @param string $key
     * @param $returnUrl
     * @param string $mode
     */
    public function __construct($key, $returnUrl, $mode = Mode::TEST)
    {
        $this->key = $key;
        $this->returnUrl = $returnUrl;

        $this->setMode($mode);
    }

    /**
     * @param PaymentTypeInterface $paymentType
     * @return Payment
     */
    public function createPayment(PaymentTypeInterface $paymentType)
    {
        $this->payment = new Payment($paymentType);
        return $this->payment;
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Heidelpay
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     * @return Heidelpay
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param mixed $returnUrl
     * @return Heidelpay
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @param $mode
     */
    private function setMode($mode)
    {
        if ($mode !== Mode::TEST) {
            $this->setSandboxMode(false);
        }
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
    //</editor-fold>
}
