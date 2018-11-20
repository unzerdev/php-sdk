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
 * @package  heidelpay/mgw_sdk/transaction_types
 */
namespace heidelpay\MgwPhpSdk\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Traits\HasCancellations;

class Charge extends AbstractTransactionType
{
    use HasCancellations;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

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

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'charges';
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
            throw new \RuntimeException('Payment type is missing!');
        }

        return [
            'customer'=> $payment->getCustomer(),
            'type' => $paymentType
        ];
    }

    //</editor-fold>

    /**
     * Full cancel of this authorization.
     * Returns the last cancellation object if charge is already canceled.
     * Creates and returns new cancellation object otherwise.
     *
     * @param float $amount
     *
     * @return Cancellation
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function cancel($amount = null): Cancellation
    {
        return $this->getHeidelpayObject()->cancelCharge($this, $amount);
    }
}
