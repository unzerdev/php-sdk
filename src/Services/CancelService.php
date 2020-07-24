<?php
/**
 * This service provides for functionalities concerning cancel transactions.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP\Services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\CancelServiceInterface;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use RuntimeException;
use function in_array;
use function is_string;

class CancelService implements CancelServiceInterface
{
    /** @var Heidelpay */
    private $heidelpay;

    /**
     * PaymentService constructor.
     *
     * @param Heidelpay $heidelpay
     */
    public function __construct(Heidelpay $heidelpay)
    {
        $this->heidelpay = $heidelpay;
    }

    //<editor-fold desc="Getters/Setters"

    /**
     * @return Heidelpay
     */
    public function getHeidelpay(): Heidelpay
    {
        return $this->heidelpay;
    }

    /**
     * @param Heidelpay $heidelpay
     *
     * @return CancelService
     */
    public function setHeidelpay(Heidelpay $heidelpay): CancelService
    {
        $this->heidelpay = $heidelpay;
        return $this;
    }

    /**
     * @return ResourceService
     */
    public function getResourceService(): ResourceService
    {
        return $this->getHeidelpay()->getResourceService();
    }

    //</editor-fold>

    //<editor-fold desc="Authorization Cancel/Reversal transaction">

    /**
     * {@inheritDoc}
     */
    public function cancelAuthorization(Authorization $authorization, float $amount = null): Cancellation
    {
        $cancellation = new Cancellation($amount);
        $cancellation->setPayment($authorization->getPayment())->setParentResource($authorization);

        /** @var Cancellation $cancellation */
        $cancellation = $this->getResourceService()->createResource($cancellation);
        return $cancellation;
    }

    /**
     * {@inheritDoc}
     */
    public function cancelAuthorizationByPayment($payment, float $amount = null): Cancellation
    {
        $authorization = $this->getResourceService()->fetchAuthorization($payment);
        return $this->cancelAuthorization($authorization, $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Charge Cancel/Refund transaction">

    /**
     * {@inheritDoc}
     */
    public function cancelChargeById(
        $payment,
        string $chargeId,
        float $amount = null,
        string $reasonCode = null,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        $charge = $this->getResourceService()->fetchChargeById($payment, $chargeId);
        return $this->cancelCharge($charge, $amount, $reasonCode, $referenceText, $amountNet, $amountVat);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCharge(
        Charge $charge,
        float $amount = null,
        string $reasonCode = null,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        $cancellation = new Cancellation($amount);
        $cancellation
            ->setReasonCode($reasonCode)
            ->setPayment($charge->getPayment())
            ->setPaymentReference($referenceText)
            ->setAmountNet($amountNet)
            ->setAmountVat($amountVat);
        $charge->addCancellation($cancellation);
        $this->getResourceService()->createResource($cancellation);

        return $cancellation;
    }

    //</editor-fold>

    //<editor-fold desc="Payment">

    /**
     * {@inheritDoc}
     */
    public function cancelPayment(
        $payment,
        float $amount = null,
        $reasonCode = CancelReasonCodes::REASON_CODE_CANCEL,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): array {
        $paymentObject = $payment;
        if (is_string($payment)) {
            $paymentObject = $this->getResourceService()->fetchPayment($payment);
        }

        if (!$paymentObject instanceof Payment) {
            throw new RuntimeException('Invalid payment object.');
        }

        $remainingToCancel = $amount;

        $cancelWholePayment = $remainingToCancel === null;
        $cancellations      = [];
        $cancellation       = null;

        if ($cancelWholePayment || $remainingToCancel > 0.0) {
            $cancellation = $this->cancelPaymentAuthorization($paymentObject, $remainingToCancel);

            if ($cancellation instanceof Cancellation) {
                $cancellations[] = $cancellation;
                $remainingToCancel = $this->updateCancelAmount($remainingToCancel, $cancellation->getAmount());
                $cancellation = null;
            }
        }

        if (!$cancelWholePayment && $remainingToCancel <= 0.0) {
            return $cancellations;
        }

        $chargeCancels = $this->cancelPaymentCharges(
            $paymentObject,
            $reasonCode,
            $referenceText,
            $amountNet,
            $amountVat,
            $remainingToCancel
        );

        return array_merge($cancellations, $chargeCancels);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelPaymentAuthorization($payment, float $amount = null): ?Cancellation
    {
        $cancellation   = null;
        $completeCancel = $amount === null;

        $authorize = $payment->getAuthorization();
        if ($authorize !== null) {
            $cancelAmount = null;
            if (!$completeCancel) {
                $remainingAuthorized = $payment->getAmount()->getRemaining();
                $cancelAmount        = $amount > $remainingAuthorized ? $remainingAuthorized : $amount;

                // do not attempt to cancel if there is nothing left to cancel
                if ($cancelAmount === 0.0) {
                    return null;
                }
            }

            try {
                $cancellation = $authorize->cancel($cancelAmount);
            } catch (HeidelpayApiException $e) {
                $this->isExceptionAllowed($e);
            }
        }

        return $cancellation;
    }

    /**
     * @param Payment $payment
     * @param string  $reasonCode
     * @param string  $referenceText
     * @param float   $amountNet
     * @param float   $amountVat
     * @param float   $remainingToCancel
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function cancelPaymentCharges(
        Payment $payment,
        $reasonCode,
        $referenceText,
        $amountNet,
        $amountVat,
        float $remainingToCancel = null
    ): array {
        $cancellations = [];
        $cancelWholePayment = $remainingToCancel === null;

        /** @var Charge $charge */
        foreach ($payment->getCharges() as $charge) {
            $cancelAmount = null;
            if (!$cancelWholePayment && $remainingToCancel <= $charge->getTotalAmount()) {
                $cancelAmount = $remainingToCancel;
            }

            try {
                $cancellation = $charge->cancel($cancelAmount, $reasonCode, $referenceText, $amountNet, $amountVat);
            } catch (HeidelpayApiException $e) {
                $this->isExceptionAllowed($e);
                continue;
            }

            if ($cancellation instanceof Cancellation) {
                $cancellations[] = $cancellation;
                $remainingToCancel = $this->updateCancelAmount($remainingToCancel, $cancellation->getAmount());
                $cancellation = null;
            }

            // stop if the amount has already been cancelled
            if (!$cancelWholePayment && $remainingToCancel <= 0) {
                break;
            }
        }
        return $cancellations;
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Throws exception if the passed exception is not to be ignored while cancelling charges or authorization.
     *
     * @param $exception
     *
     * @throws HeidelpayApiException
     */
    private function isExceptionAllowed(HeidelpayApiException $exception): void
    {
        $allowedErrors = [
            ApiResponseCodes::API_ERROR_ALREADY_CANCELLED,
            ApiResponseCodes::API_ERROR_ALREADY_CHARGED,
            ApiResponseCodes::API_ERROR_TRANSACTION_CANCEL_NOT_ALLOWED,
            ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK
        ];

        if (!in_array($exception->getCode(), $allowedErrors, true)) {
            throw $exception;
        }
    }

    /**
     * Calculates and returns the remaining amount to cancel.
     * Returns null if the whole payment is to be canceled.
     *
     * @param float|null $remainingToCancel
     * @param float      $amount
     *
     * @return float|null
     */
    private function updateCancelAmount($remainingToCancel, float $amount): ?float
    {
        $cancelWholePayment = $remainingToCancel === null;
        if (!$cancelWholePayment) {
            $remainingToCancel -= $amount;
        }
        return $remainingToCancel;
    }

    //</editor-fold>
}
