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

use heidelpay\NmgPhpSdk\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class Card extends BasePaymentType
{
    /** @var string $pan */
    private $pan;

    /** @var string $expirationDate */
    private $expirationDate;

    /** @var int $cvc */
    private $cvc;

    /** @var string $holder */
    private $holder = '';

    /**
     * Card constructor.
     * @param string $pan
     * @param string $expirationDate
     */
    public function __construct($pan, $expirationDate)
    {
        $this->pan = $pan;
        $this->expirationDate = $expirationDate;
    }

    /**
     * @param HeidelpayParentInterface $parent
     * @param string $pan
     * @param string $expirationDate
     * @param int $cvc
     * @return Card
     */
    public static function newCard(HeidelpayParentInterface $parent, $pan, $expirationDate, $cvc): Card
    {
        $card = new self($pan, $expirationDate);
//        $card->pan = $pan;
//        $card->expirationDate = $expirationDate;
        $card->cvc = $cvc;
        $card->setParentResource($parent);

        return $card;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return Charge
     */
    public function charge($amount, $currency): Charge
    {
        return new Charge($this);
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return Authorization
     */
    public function authorize($amount, $currency): Authorization
    {
        return new Authorization($this);
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
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param string $expirationDate
     * @return Card
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
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
