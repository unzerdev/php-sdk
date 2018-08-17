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

class Card extends BasePaymentType
{
    /** @var string $pan */
    protected $pan;

    /** @var string $expiryDate */
    protected $expiryDate;

    /** @var int $cvc */
    protected $cvc;

    /** @var string $holder */
    protected $holder = '';

    /**
     * Card constructor.
     * @param string $pan
     * @param string $expiryDate
     */
    public function __construct($pan, $expiryDate)
    {
        $this->setAuthorizable(true);
        $this->setCancelable(true);
        $this->setChargeable(true);

        $this->pan = $pan;
        $this->expiryDate = $expiryDate;
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
    public function getPan(): string
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     * @return Card
     */
    public function setPan($pan): Card
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
     * @return int
     */
    public function getCvc(): int
    {
        return $this->cvc;
    }

    /**
     * @param int $cvc
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
