<?php
/**
 * This represents the card payment type which supports credit card as well as debit card payments.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/PaymentTypes
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

    /** @var string $brand */
    private $brand = '';

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
        $this->setExpiryDate($expiryDate);

        parent::__construct();
    }

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    protected function handleResponse(\stdClass $response)
    {
        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return;
        }

        // todo change cvv to cvc
        if (isset($response->cvv)) {
            $this->setCvc($response->cvv);
        }
        if (isset($response->number)) {
            $this->setNumber($response->number);
        }
        if (isset($response->expiry)) {
            $this->setExpiryDate($response->expiry);
        }
        if (isset($response->brand)) {
            $this->setBrand($response->brand);
        }

        parent::handleResponse($response);
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
        $expiryDateParts = explode('/', $expiryDate);
        if (\count($expiryDateParts) > 1) {
            $this->expiryDate = date('m/Y', mktime(0, 0, 0, $expiryDateParts[0], 1, $expiryDateParts[1]));
        }
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

    /**
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * Setter for brand property.
     * Will be set internally on create or fetch card.
     *
     * @param string $brand
     * @return Card
     */
    private function setBrand(string $brand): Card
    {
        $this->brand = $brand;
        return $this;
    }
    //</editor-fold>
}
