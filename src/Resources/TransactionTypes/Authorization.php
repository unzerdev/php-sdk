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
 * @package  heidelpay/mgw_sdk/transaction_types
 */
namespace heidelpay\MgwPhpSdk\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Traits\HasCancellations;

class Authorization extends AbstractTransactionType
{
    use HasCancellations;

    /** @var float $amount */
    protected $amount = 0.0;

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
     * @return float
     */
    public function getAmount(): float
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
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return AbstractHeidelpayResource
     */
    public function setCurrency($currency): AbstractHeidelpayResource
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return AbstractHeidelpayResource
     */
    public function setReturnUrl($returnUrl): AbstractHeidelpayResource
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getIban(): string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     *
     * @return Authorization
     */
    public function setIban(string $iban): Authorization
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     *
     * @return Authorization
     */
    public function setBic(string $bic): Authorization
    {
        $this->bic = $bic;
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
     *
     * @return Authorization
     */
    public function setHolder(string $holder): Authorization
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescriptor(): string
    {
        return $this->descriptor;
    }

    /**
     * @param string $descriptor
     *
     * @return Authorization
     */
    public function setDescriptor(string $descriptor): Authorization
    {
        $this->descriptor = $descriptor;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'authorize';
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     */
    public function getLinkedResources(): array
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $paymentType = $payment ? $payment->getPaymentType() : null;
        if (!$paymentType instanceof BasePaymentType) {
            throw new \RuntimeException('Payment type is undefined!');
        }

        return [
            'customer'=> $payment->getCustomer(),
            'type' => $paymentType
        ];
    }

    //</editor-fold>

    /**
     * Full cancel of this authorization.
     *
     * @param null $amount
     *
     * @return Cancellation
     *
     * @throws \RuntimeException
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
     * @throws \RuntimeException
     */
    public function charge($amount = null): Charge
    {
        $payment = $this->getPayment();
        if (!$payment instanceof Payment) {
            throw new \RuntimeException('Payment object is missing. Try fetching the object first!');
        }
        return $this->getHeidelpayObject()->chargeAuthorization($payment->getId(), $amount);
    }
}
