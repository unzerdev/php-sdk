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

class Card implements PaymentTypeInterface
{
    /** @var string $pan */
    private $pan;

    /** @var string $expirationMonth */
    private $expirationMonth;

    /** @var string $expirationYear */
    private $expirationYear;

    /** @var int $cvc */
    private $cvc;

    /** @var string $holder */
    private $holder = '';

    /**
     * Card constructor.
     * @param string $pan
     * @param string $expirationMonth
     * @param string $expirationYear
     * @param int $cvc
     */
    public function __construct($pan, $expirationMonth, $expirationYear, $cvc)
    {
        $this->pan = $pan;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->cvc = $cvc;
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getPan()
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     * @return Card
     */
    public function setPan($pan)
    {
        $this->pan = $pan;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationMonth()
    {
        return $this->expirationMonth;
    }

    /**
     * @param string $expirationMonth
     * @return Card
     */
    public function setExpirationMonth($expirationMonth)
    {
        $this->expirationMonth = $expirationMonth;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationYear()
    {
        return $this->expirationYear;
    }

    /**
     * @param string $expirationYear
     * @return Card
     */
    public function setExpirationYear($expirationYear)
    {
        $this->expirationYear = $expirationYear;
        return $this;
    }

    /**
     * @return int
     */
    public function getCvc()
    {
        return $this->cvc;
    }

    /**
     * @param int $cvc
     * @return Card
     */
    public function setCvc($cvc)
    {
        $this->cvc = $cvc;
        return $this;
    }

    /**
     * @return string
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     * @return Card
     */
    public function setHolder($holder)
    {
        $this->holder = $holder;
        return $this;
    }
    //</editor-fold>
}
