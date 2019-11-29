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

use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\CancelServiceInterface;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;

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
    public function cancelAuthorization(Authorization $authorization, $amount = null): Cancellation
    {
        $cancellation = new Cancellation($amount);
        $cancellation->setPayment($authorization->getPayment());
        $authorization->addCancellation($cancellation);
        $this->getResourceService()->createResource($cancellation);

        return $cancellation;
    }

    /**
     * {@inheritDoc}
     */
    public function cancelAuthorizationByPayment($payment, $amount = null): Cancellation
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
        $chargeId,
        float $amount = null,
        string $reasonCode = null,
        string $paymentReference = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        $charge = $this->getResourceService()->fetchChargeById($payment, $chargeId);
        return $this->cancelCharge($charge, $amount, $reasonCode, $paymentReference, $amountNet, $amountVat);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCharge(
        Charge $charge,
        $amount = null,
        string $reasonCode = null,
        string $paymentReference = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        $cancellation = new Cancellation($amount);
        $cancellation
            ->setReasonCode($reasonCode)
            ->setPayment($charge->getPayment())
            ->setPaymentReference($paymentReference)
            ->setAmountNet($amountNet)
            ->setAmountVat($amountVat);
        $charge->addCancellation($cancellation);
        $this->getResourceService()->createResource($cancellation);

        return $cancellation;
    }

    //</editor-fold>
}
