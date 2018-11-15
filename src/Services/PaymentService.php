<?php
/**
 * This service provides for functionalities concerning payment transactions.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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
 * @package  heidelpay/mgw_sdk/services
 */
namespace heidelpay\MgwPhpSdk\Services;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;

class PaymentService
{
    /** @var Heidelpay */
    private $heidelpay;

    /** @var ResourceService $resourceService */
    private $resourceService;

    /**
     * PaymentService constructor.
     *
     * @param Heidelpay $heidelpay
     */
    public function __construct(Heidelpay $heidelpay)
    {
        $this->heidelpay = $heidelpay;
        $this->resourceService = $heidelpay->getResourceService();
    }

    //<editor-fold desc="Helpers">

    /**
     * Create a Payment object with the given properties.
     *
     * @param BasePaymentType|string $paymentType
     * @param Customer|string|null   $customer
     *
     * @return Payment The resulting Payment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    private function createPayment($paymentType, $customer = null): AbstractHeidelpayResource
    {
        return (new Payment($this->heidelpay))->setPaymentType($paymentType)->setCustomer($customer);
    }

    //</editor-fold>

    //<editor-fold desc="Transactions">

    //<editor-fold desc="Authorize transaction">

    /**
     * Perform an Authorization transaction and return the corresponding Authorization object.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentType
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     * @param string|null          $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl, $customer = null, $orderId = null): AbstractTransactionType
    {
        $payment = $this->createPayment($paymentType, $customer);
        return $this->authorizeWithPayment($amount, $currency, $payment, $returnUrl, $customer, $orderId);
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param $amount
     * @param $currency
     * @param Payment $payment
     * @param $returnUrl
     * @param string|null $customer
     * @param string|null $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function authorizeWithPayment(
        $amount,
        $currency,
        Payment $payment,
        $returnUrl = null,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $authorization = (new Authorization($amount, $currency, $returnUrl))->setOrderId($orderId);
        $payment->setAuthorization($authorization)->setCustomer($customer);
        $this->resourceService->create($authorization);
        return $authorization;
    }

    //</editor-fold>

    //<editor-fold desc="Charge transaction">

    /**
     * Charge the given amount and currency on the given PaymentType resource.
     *
     * @param float                  $amount
     * @param string                 $currency
     * @param BasePaymentType|string $paymentType
     * @param string                 $returnUrl
     * @param Customer|string|null   $customer
     * @param string|null            $orderId
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $payment = $this->createPayment($paymentType, $customer);
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setParentResource($payment)->setPayment($payment);
        $charge->setOrderId($orderId);
        $payment->addCharge($charge);
        $this->resourceService->create($charge);

        return $charge;
    }

    /**
     * Charge the given amount on the payment with the given id.
     * Perform a full charge by leaving the amount null.
     *
     * @param string|Payment $payment
     * @param null           $amount
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function chargeAuthorization($payment, $amount = null): AbstractTransactionType
    {
        $paymentObject = $payment;

        if (\is_string($payment)) {
            $paymentObject = $this->resourceService->fetchPayment($payment);
        }

        return $this->chargePayment($paymentObject, $amount);
    }

    /**
     * Charge the given amount on the given payment object with the given currency.
     *
     * @param Payment $payment
     * @param null    $amount
     * @param null    $currency
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function chargePayment(Payment $payment, $amount = null, $currency = null): AbstractTransactionType
    {
        $charge = new Charge($amount, $currency);
        $charge->setParentResource($payment)->setPayment($payment);
        $payment->addCharge($charge);
        $this->resourceService->create($charge);
        return $charge;
    }

    //</editor-fold>

    //<editor-fold desc="Authorization Cancel/Reversal transaction">

    /**
     * Perform a Cancellation transaction with the given amount for the given Authorization.
     *
     * @param Authorization $authorization
     * @param null          $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function cancelAuthorization(Authorization $authorization, $amount = null): AbstractTransactionType
    {
        $cancellation = new Cancellation($amount);
        $authorization->addCancellation($cancellation);
        $cancellation->setPayment($authorization->getPayment());
        $this->resourceService->create($cancellation);

        return $cancellation;
    }

    /**
     * Creates a Cancellation transaction for the given Authorization object.
     *
     * @param Payment|string $payment
     * @param null           $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function cancelAuthorizationByPayment($payment, $amount = null): AbstractTransactionType
    {
        return $this->cancelAuthorization($this->resourceService->fetchAuthorization($payment), $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Charge Cancel/Refund transaction">

    /**
     * Create a Cancellation transaction for the charge with the given id belonging to the given Payment object.
     *
     * @param Payment|string $payment
     * @param string         $chargeId
     * @param null           $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function cancelChargeById($payment, $chargeId, $amount = null): AbstractTransactionType
    {
        return $this->cancelCharge($this->resourceService->fetchChargeById($payment, $chargeId), $amount);
    }

    /**
     * Create a Cancellation transaction for the given Charge resource.
     *
     * @param Charge $charge
     * @param $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function cancelCharge(Charge $charge, $amount = null): AbstractTransactionType
    {
        $cancellation = new Cancellation($amount);
        $charge->addCancellation($cancellation);
        $cancellation->setPayment($charge->getPayment());
        $this->resourceService->create($cancellation);

        return $cancellation;
    }

    //</editor-fold>

    //<editor-fold desc="Shipment transaction">

    /**
     * Creates a Shipment transaction for the given Payment object.
     *
     * @param Payment|string $payment
     *
     * @return Shipment Resulting Shipment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function ship($payment): AbstractHeidelpayResource
    {
        $paymentObject = $payment;

        if (\is_string($payment)) {
            $paymentObject = $this->resourceService->fetchPayment($payment);
        }

        if (!$paymentObject instanceof Payment) {
            throw new \RuntimeException('Payment object is not set.');
        }

        $shipment = new Shipment();
        $paymentObject->addShipment($shipment);
        return $this->resourceService->create($shipment);
    }

    //</editor-fold>
    
    //</editor-fold>
}
