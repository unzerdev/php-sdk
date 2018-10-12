<?php
/**
 * This represents the charge transaction.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/transaction_types
 */
namespace heidelpay\MgwPhpSdk\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Exceptions\MissingResourceException;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\MgwPhpSdk\Traits\HasCancellationsTrait;

class Charge extends AbstractTransactionType
{
    use HasCancellationsTrait;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string $uniqueId */
    private $uniqueId;

    /** @var string $orderId */
    protected $orderId;

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

        parent::__construct(null);
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
     * @return HeidelpayResourceInterface
     */
    public function setAmount($amount): HeidelpayResourceInterface
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
     * @return HeidelpayResourceInterface
     */
    public function setCurrency($currency): HeidelpayResourceInterface
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
     * @return HeidelpayResourceInterface
     */
    public function setReturnUrl($returnUrl): HeidelpayResourceInterface
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     *
     * @return HeidelpayResourceInterface
     */
    public function setUniqueId($uniqueId): HeidelpayResourceInterface
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return Charge
     */
    public function setOrderId($orderId): Charge
    {
        $this->orderId = $orderId;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'charges';
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkedResources(): array
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $paymentType = $payment ? $payment->getPaymentType() : null;
        if (!$paymentType instanceof PaymentTypeInterface) {
            throw new MissingResourceException();
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
     */
    public function cancel($amount = null): Cancellation
    {
        return $this->getHeidelpayObject()->cancelCharge($this, $amount);
    }
}
