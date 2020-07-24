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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\TransactionTypes
 */
namespace heidelpayPHP\Resources\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Traits\HasCancellations;
use heidelpayPHP\Traits\HasInvoiceId;
use RuntimeException;

class Authorization extends AbstractTransactionType
{
    use HasCancellations;
    use HasInvoiceId;

    /** @var float $amount */
    protected $amount = 0.0;

    /** @var string $currency */
    protected $currency;

    /** @var string $returnUrl */
    protected $returnUrl;

    /** @var bool $card3ds */
    protected $card3ds;

    /** @var string $paymentReference */
    protected $paymentReference;

    /** @var string $externalOrderId*/
    private $externalOrderId;

    /** @var string $zgReferenceId*/
    private $zgReferenceId;

    /** @var string $PDFLink*/
    private $PDFLink;

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
    }

    //<editor-fold desc="Setters/Getters">

    /**
     * @return float|null
     */
    public function getAmount(): ?float
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
        $this->amount = $amount !== null ? round($amount, 4) : null;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getCancelledAmount(): ?float
    {
        $amount = 0.0;
        foreach ($this->getCancellations() as $cancellation) {
            /** @var Cancellation $cancellation */
            $amount += $cancellation->getAmount();
        }

        return $amount;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
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
    public function getReturnUrl(): ?string
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
    public function isCard3ds(): ?bool
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

    /**
     * @return string|null
     */
    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    /**
     * @param $paymentReference
     *
     * @return Authorization
     */
    public function setPaymentReference($paymentReference): Authorization
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalOrderId(): ?string
    {
        return $this->externalOrderId;
    }

    /**
     * @param string|null $externalOrderId
     *
     * @return Authorization
     */
    protected function setExternalOrderId($externalOrderId): Authorization
    {
        $this->externalOrderId = $externalOrderId;
        return $this;
    }

    /**
     * Returns the reference Id of the insurance provider if applicable.
     *
     * @return string|null
     */
    public function getZgReferenceId(): ?string
    {
        return $this->zgReferenceId;
    }

    /**
     * Sets the reference Id of the insurance provider.
     *
     * @param string|null $zgReferenceId
     *
     * @return Authorization
     */
    protected function setZgReferenceId($zgReferenceId): Authorization
    {
        $this->zgReferenceId = $zgReferenceId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPDFLink(): ?string
    {
        return $this->PDFLink;
    }

    /**
     * @param string|null $PDFLink
     *
     * @return Authorization
     */
    protected function setPDFLink($PDFLink): Authorization
    {
        $this->PDFLink = $PDFLink;
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
