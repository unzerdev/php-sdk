<?php
/**
 * This represents the authorization transaction.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/transaction_types
 */
namespace heidelpayPHP\Resources\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Traits\HasCancellations;
use RuntimeException;

class Authorization extends AbstractTransactionType
{
    use HasCancellations;

    /** @var float $amount */
    protected $amount = 0.0;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var bool $card3ds */
    protected $card3ds;

    /**
     * Authorization constructor.
     *
     * @param float  $amount
     * @param string $currency
     * @param string $returnUrl
     */
    public function __construct($amount = null, $currency = null, $returnUrl = null)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setReturnUrl($returnUrl);

        parent::__construct();
    }

    //<editor-fold desc="Setters/Getters">

    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return self
     */
    public function setAmount($amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency($currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return self
     */
    public function setReturnUrl($returnUrl): self
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isCard3ds()
    {
        return $this->card3ds;
    }

    /**
     * @param bool|null $card3ds
     *
     * @return Authorization
     */
    public function setCard3ds($card3ds): Authorization
    {
        $this->card3ds = $card3ds;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'authorize';
    }

    //</editor-fold>

    /**
     * Full cancel of this authorization.
     *
     * @param null $amount
     *
     * @return Cancellation
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function cancel($amount = null): Cancellation
    {
        return $this->getHeidelpayObject()->cancelAuthorization($this, $amount);
    }

    /**
     * Charge authorization.
     *
     * @param null $amount
     *
     * @return Charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function charge($amount = null): Charge
    {
        $payment = $this->getPayment();
        if (!$payment instanceof Payment) {
            throw new RuntimeException('Payment object is missing. Try fetching the object first!');
        }
        return $this->getHeidelpayObject()->chargeAuthorization($payment, $amount);
    }
}
