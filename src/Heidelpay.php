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

use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Constants\Mode;
use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;

class Heidelpay implements HeidelpayParentInterface
{
    private $key;
    private $locale;

    /** @var Payment $payment */
    private $payment;

    /** @var bool */
    private $sandboxMode = true;

    /**
     * Heidelpay constructor.
     *
     * @param string $key
     * @param string $locale
     * @param string $mode
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN, $mode = Mode::TEST)
    {
        $this->key = $key;
        $this->locale = $locale;

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
        if ($this->payment instanceof Payment) {
            return $this->payment;
        }

        if (empty($this->paymentId)) {
            throw new MissingResourceException('Payment object does not exist.');
        }

        // todo: fetch payment from api and return it

        return $this->payment;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Heidelpay
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="ParentIF">
    /**
     * Returns the heidelpay root object.
     *
     * @return Heidelpay
     */
    public function getHeidelpayObject()
    {
        return $this;
    }

    /**
     * Returns the url string for this resource.
     *
     * @return string
     */
    public function getUri()
    {
        return '';
    }
    //</editor-fold>
}
