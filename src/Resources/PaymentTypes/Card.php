<?php
/**
 * This represents the card payment type which supports credit card as well as debit card payments.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Resources\EmbeddedResources\CardDetails;
use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Traits\CanPayout;
use UnzerSDK\Traits\CanRecur;
use UnzerSDK\Validators\ExpiryDateValidator;
use RuntimeException;
use stdClass;

class Card extends BasePaymentType
{
    use CanDirectCharge;
    use CanAuthorize;
    use CanPayout;
    use CanRecur;

    /** @var string $number */
    protected $number;

    /** @var string $expiryDate */
    protected $expiryDate;

    /** @var string $cvc */
    protected $cvc;

    /** @var string $cardHolder */
    protected $cardHolder = '';

    /** @var bool $card3ds */
    protected $card3ds;

    /** @var string */
    protected $email;

    /** @var string $brand */
    private $brand = '';

    /** @var CardDetails $cardDetails */
    private $cardDetails;

    /**
     * Card constructor.
     *
     * @param string      $number
     * @param string      $expiryDate
     * @param string|null $email
     */
    public function __construct($number, $expiryDate, $email = null)
    {
        $this->setNumber($number);
        $this->setExpiryDate($expiryDate);
        $this->setEmail($email);
    }

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
     *
     * @return Card
     */
    public function setNumber($pan): Card
    {
        $this->number = $pan;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpiryDate(): ?string
    {
        return $this->expiryDate;
    }

    /**
     * @param string $expiryDate
     *
     * @return Card
     *
     * @throws RuntimeException
     */
    public function setExpiryDate($expiryDate): Card
    {
        // Null value is allowed to be able to fetch a card object with nothing but the id set.
        if ($expiryDate === null) {
            return $this;
        }

        if (!ExpiryDateValidator::validate($expiryDate)) {
            throw new RuntimeException("Invalid expiry date format: \"{$expiryDate}\". Allowed formats are 'm/Y' and 'm/y'.");
        }
        $expiryDateParts = explode('/', $expiryDate);
        $this->expiryDate = date('m/Y', mktime(0, 0, 0, $expiryDateParts[0], 1, $expiryDateParts[1]));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCvc(): ?string
    {
        return $this->cvc;
    }

    /**
     * @param string $cvc
     *
     * @return Card
     */
    public function setCvc($cvc): Card
    {
        $this->cvc = $cvc;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCardHolder(): ?string
    {
        return $this->cardHolder;
    }

    /**
     * @param string $cardHolder
     *
     * @return Card
     */
    public function setCardHolder($cardHolder): Card
    {
        $this->cardHolder = $cardHolder;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function get3ds(): ?bool
    {
        return $this->card3ds;
    }

    /**
     * @param bool|null $card3ds
     *
     * @return Card
     */
    public function set3ds($card3ds): Card
    {
        $this->card3ds = $card3ds;
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
     *
     * @return Card
     */
    protected function setBrand(string $brand): Card
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return CardDetails|null
     */
    public function getCardDetails(): ?CardDetails
    {
        return $this->cardDetails;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return Card
     */
    public function setEmail(?string $email): Card
    {
        $this->email = $email;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * Rename internal property names to external property names.
     *
     * {@inheritDoc}
     */
    public function expose()
    {
        $exposeArray = parent::expose();
        if (isset($exposeArray['card3ds'])) {
            $exposeArray['3ds'] = $exposeArray['card3ds'];
            unset($exposeArray['card3ds']);
        }
        return $exposeArray;
    }

    /**
     * {@inheritDoc}
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        if (isset($response->cardDetails)) {
            $this->cardDetails = new CardDetails();
            $this->cardDetails->handleResponse($response->cardDetails);
        }
    }

    //</editor-fold>
}
