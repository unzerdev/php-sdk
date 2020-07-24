<?php
/**
 * This represents the cancel transaction.
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

use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use function in_array;

class Cancellation extends AbstractTransactionType
{
    /**
     * The cancellation amount will be transferred as grossAmount in case of Hire Purchase payment type.
     *
     * @var float $amount
     */
    protected $amount;

    /** @var string $reasonCode */
    protected $reasonCode;

    /** @var string $paymentReference */
    protected $paymentReference;

    /**
     * The net value of the cancellation amount (Hire Purchase only).
     *
     * @var float $amountNet
     */
    protected $amountNet;

    /**
     * The vat value of the cancellation amount (Hire Purchase only).
     *
     * @var float $amountVat
     */
    protected $amountVat;

    /**
     * Authorization constructor.
     *
     * @param float $amount The amount to be cancelled, is transferred as grossAmount in case of Hire Purchase.
     */
    public function __construct($amount = null)
    {
        $this->setAmount($amount);
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * Returns the cancellationAmount (equals grossAmount in case of Hire Purchase).
     *
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Sets the cancellationAmount (equals grossAmount in case of Hire Purchase).
     *
     * @param float $amount
     *
     * @return Cancellation
     */
    public function setAmount($amount): Cancellation
    {
        $this->amount = $amount !== null ? round($amount, 4) : null;
        return $this;
    }

    /**
     * Returns the reason code of the cancellation if set.
     *
     * @return string|null
     */
    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }

    /**
     * Sets the reason code of the cancellation.
     *
     * @param string|null $reasonCode
     *
     * @return Cancellation
     */
    public function setReasonCode($reasonCode): Cancellation
    {
        if (in_array($reasonCode, array_merge(CancelReasonCodes::REASON_CODE_ARRAY, [null]), true)) {
            $this->reasonCode = $reasonCode;
        }
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
     * @param string|null $paymentReference
     *
     * @return Cancellation
     */
    public function setPaymentReference($paymentReference): Cancellation
    {
        $this->paymentReference = $paymentReference;
        return $this;
    }

    /**
     * Returns the net value of the amount to be cancelled.
     * This is needed for Hire Purchase (FlexiPay Rate) payment types only.
     *
     * @return float|null
     */
    public function getAmountNet(): ?float
    {
        return $this->amountNet;
    }

    /**
     * Sets the net value of the amount to be cancelled.
     * This is needed for Hire Purchase (FlexiPay Rate) payment types only.
     *
     * @param float|null $amountNet The net value of the amount to be cancelled (Hire Purchase only).
     *
     * @return Cancellation The resulting cancellation object.
     */
    public function setAmountNet($amountNet): Cancellation
    {
        $this->amountNet = $amountNet;
        return $this;
    }

    /**
     * Returns the vat value of the cancellation amount.
     * This is needed for Hire Purchase (FlexiPay Rate) payment types only.
     *
     * @return float|null
     */
    public function getAmountVat(): ?float
    {
        return $this->amountVat;
    }

    /**
     * Sets the vat value of the cancellation amount.
     * This is needed for Hire Purchase (FlexiPay Rate) payment types only.
     *
     * @param float|null $amountVat
     *
     * @return Cancellation
     */
    public function setAmountVat($amountVat): Cancellation
    {
        $this->amountVat = $amountVat;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    public function expose()
    {
        $exposeArray = parent::expose();
        $payment = $this->getPayment();
        if (isset($exposeArray['amount'])
            && $payment instanceof Payment && $payment->getPaymentType() instanceof HirePurchaseDirectDebit) {
            $exposeArray['amountGross'] = $exposeArray['amount'];
            unset($exposeArray['amount']);
        }
        return $exposeArray;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'cancels';
    }

    //</editor-fold>
}
