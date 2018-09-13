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

class Card extends BasePaymentType
{
    // todo rename pan to number
    /** @var string $pan */
    protected $pan;

    /** @var string $expiryDate */
    protected $expiryDate;

    /** @var string $cvc */
    protected $cvc;

    /** @var string $holder */
    protected $holder = '';

    /**
     * Card constructor.
     * @param string $number
     * @param string $expiryDate
     */
    public function __construct($number, $expiryDate)
    {
        $this->setAuthorizable(true)
             ->setChargeable(true);

        $this->setNumber($number);
        $this->expiryDate = $expiryDate;

        parent::__construct();
    }

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/cards';
    }
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     * @return Card
     */
    public function setNumber($pan): Card
    {
        $this->pan = $pan;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiryDate(): string
    {
        return $this->expiryDate;
    }

    /**
     * @param string $expiryDate
     * @return Card
     */
    public function setExpiryDate($expiryDate): Card
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCvc(): string
    {
        return $this->cvc;
    }

    /**
     * @param string $cvc
     * @return Card
     */
    public function setCvc($cvc): Card
    {
        $this->cvc = $cvc;
        return $this;
    }

    /**
     * @return string
     */
    public function getHolder(): string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     * @return Card
     */
    public function setHolder($holder): Card
    {
        $this->holder = $holder;
        return $this;
    }
    //</editor-fold>
}
