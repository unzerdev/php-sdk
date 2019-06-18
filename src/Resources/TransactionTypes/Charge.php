<?php
/**
 * This represents the charge transaction.
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
use heidelpayPHP\Traits\HasCancellations;
use heidelpayPHP\Traits\HasInvoiceId;
use RuntimeException;

class Charge extends AbstractTransactionType
{
    use HasCancellations;
    use HasInvoiceId;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string $iban */
    private $iban;

    /** @var string bic */
    private $bic;

    /** @var string $holder */
    private $holder;

    /** @var string $descriptor */
    private $descriptor;

    /** @var string $paymentReference */
    protected $paymentReference;

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
     * Returns the IBAN of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return self
     */
    protected function setIban(string $iban): self
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * Returns the BIC of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     *
     * @return self
     */
    protected function setBic(string $bic): self
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * Returns the holder of the account the customer needs to transfer the amount to.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     *
     * @return self
     */
    protected function setHolder(string $holder): self
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * Returns the Descriptor the customer needs to use when transferring the amount.
     * E. g. invoice, prepayment, etc.
     *
     * @return string|null
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     *
     * @return self
     */
    protected function setDescriptor(string $descriptor): self
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * @param string|null $paymentReference
     *
     * @return Charge
     */
    public function setPaymentReference($paymentReference): Charge
    {
        $this->paymentReference = $paymentReference;
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
     * @return Charge
     */
    public function setCard3ds($card3ds): Charge
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
        return 'charges';
    }

    //</editor-fold>

    /**
     * Full cancel of this authorization.
     * Returns the last cancellation object if charge is already canceled.
     * Creates and returns new cancellation object otherwise.
     *
     * @param float|null  $amount
     * @param string|null $reasonCode
     * @param string|null $paymentReference
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function cancel($amount = null, string $reasonCode = null, string $paymentReference = null): Cancellation
    {
        return $this->getHeidelpayObject()->cancelCharge($this, $amount, $reasonCode, $paymentReference);
    }
}
