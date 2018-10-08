<?php
/**
 * This represents the card payment type which supports credit card as well as debit card payments.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
    protected function setBrand(string $brand): Card
    {
        $this->brand = $brand;
        return $this;
    }
    //</editor-fold>
    //</editor-fold>
}
