<?php
/**
 * This represents the card payment type which supports credit card as well as debit card payments.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 * @package  heidelpay/mgw_sdk/payment_types
 */
namespace heidelpay\MgwPhpSdk\Resources\PaymentTypes;

class Card extends BasePaymentType
{
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

    //<editor-fold desc="Properties">
    /** @var string $number */
    protected $number;

    /** @var string $expiryDate */
    protected $expiryDate;

    /** @var string $cvc */
    protected $cvc;

    /** @var string $holder */
    protected $holder = '';

    /** @var string $brand */
    private $brand = '';

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $pan
     * @return Card
     */
    public function setNumber($pan): Card
    {
        $this->number = $pan;
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
    //</editor-fold>

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function handleResponse(\stdClass $response)
    {
        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return;
        }

        if (isset($response->cvc)) {
            $this->setCvc($response->cvc);
        }
        if (isset($response->number)) {
            $this->setNumber($response->number);
        }
        if (isset($response->expiryDate)) {
            $this->setExpiryDate($response->expiryDate);
        }
        if (isset($response->brand)) {
            $this->setBrand($response->brand);
        }

        parent::handleResponse($response);
    }
    //</editor-fold>
}
