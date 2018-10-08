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
 *
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
use heidelpay\MgwPhpSdk\Traits\HasValueHelper;

class Charge extends AbstractTransactionType
{
    use HasCancellationsTrait;
    use HasValueHelper;

    /** @var float $amount */
    protected $amount;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var string $uniqueId */
    private $uniqueId;

    /** @var string $redirectUrl */
    private $redirectUrl;

    /**
     * Authorization constructor.
     * @param float $amount
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
     * @return HeidelpayResourceInterface
     */
    public function setAmount($amount): HeidelpayResourceInterface
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
     * @return HeidelpayResourceInterface
     */
    public function setCurrency($currency): HeidelpayResourceInterface
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
     * @return HeidelpayResourceInterface
     */
    public function setReturnUrl($returnUrl): HeidelpayResourceInterface
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     * @return HeidelpayResourceInterface
     */
    public function setUniqueId(string $uniqueId): HeidelpayResourceInterface
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * Returns true if the charge is fully canceled.
     * todo: canceled as state?
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        $canceledAmount = 0.0;

        /** @var Cancellation $cancellation */
        foreach ($this->cancellations as $cancellation) {
            $canceledAmount += $cancellation->getAmount();
        }

        return $this->amountIsGreaterThanOrEqual($canceledAmount, $this->amount);
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * @param string|null $redirectUrl
     * @return Charge
     */
    public function setRedirectUrl($redirectUrl): Charge
    {
        $this->redirectUrl = $redirectUrl;
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

    /**
     * {@inheritDoc}
     */
    public function handleResponse(\stdClass $response)
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        if (isset($response->resources->paymentId)) {
            $payment->setId($response->resources->paymentId);
        }

        if (isset($response->redirectUrl)) {
            // todo: maybe just one of these applies depending on answer #10 https://heidelpay.atlassian.net/wiki/spaces/ID/pages/359727164/Q+and+A+Suggestions
            $this->setRedirectUrl($response->redirectUrl);
            $payment->setRedirectUrl($response->redirectUrl);
        }

        parent::handleResponse($response);
    }
    //</editor-fold>

    /**
     * Full cancel of this authorization.
     * Returns the last cancellation object if charge is already canceled.
     * Creates and returns new cancellation object otherwise.
     *
     * @param float $amount
     * @return Cancellation
     */
    public function cancel($amount = null): Cancellation
    {
        if ($this->isCanceled()) {
            return end($this->cancellations);
        }

        $cancellation = new Cancellation($amount);
        $this->addCancellation($cancellation);
        $cancellation->setParentResource($this);
        $cancellation->setPayment($this->getPayment());
        $cancellation->create();

        return $cancellation;
    }
}
