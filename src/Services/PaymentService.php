<?php
/**
 * This service provides for functionalities concerning payment transactions.
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
 * @package  heidelpayPHP/services
 */
namespace heidelpayPHP\Services;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use RuntimeException;

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

    //<editor-fold desc="Getters/Setters">

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
     * @return PaymentService
     */
    public function setHeidelpay(Heidelpay $heidelpay): PaymentService
    {
        $this->heidelpay = $heidelpay;
        return $this;
    }

    /**
     * @return ResourceService
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * @param ResourceService $resourceService
     *
     * @return PaymentService
     */
    public function setResourceService(ResourceService $resourceService): PaymentService
    {
        $this->resourceService = $resourceService;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Create a Payment object with the given properties.
     *
     * @param BasePaymentType|string $paymentType
     *
     * @return Payment The resulting Payment object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    private function createPayment($paymentType): AbstractHeidelpayResource
    {
        return (new Payment($this->heidelpay))->setPaymentType($paymentType);
    }

    //</editor-fold>

    //<editor-fold desc="Transactions">

    //<editor-fold desc="Authorize transaction">

    /**
     * Perform an Authorization transaction and return the corresponding Authorization object.
     *
     * @param float                  $amount
     * @param string                 $currency
     * @param BasePaymentType|string $paymentType
     * @param string                 $returnUrl
     * @param Customer|string|null   $customer
     * @param string|null            $orderId
     * @param Metadata|string|null   $metadata
     * @param Basket|null            $basket      The Basket object corresponding to the payment.
     *                                            The Basket object will be created automatically if it does not exist
     *                                            yet (i.e. has no id).
     * @param bool|null              $card3ds     Enables 3ds channel for credit cards if available. This parameter is
     *                                            optional and will be ignored if not applicable.
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorize(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null
    ): AbstractTransactionType {
        $payment = $this->createPayment($paymentType);
        return $this->authorizeWithPayment(
            $amount,
            $currency,
            $payment,
            $returnUrl,
            $customer,
            $orderId,
            $metadata,
            $basket,
            $card3ds
        );
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float                $amount
     * @param string               $currency
     * @param Payment              $payment
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     * @param string|null          $orderId
     * @param Metadata|string|null $metadata
     * @param Basket|null          $basket    The Basket object corresponding to the payment.
     *                                        The Basket object will be created automatically if it does not exist
     *                                        yet (i.e. has no id).
     * @param bool|null            $card3ds   Enables 3ds channel for credit cards if available. This parameter is
     *                                        optional and will be ignored if not applicable.
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function authorizeWithPayment(
        $amount,
        $currency,
        Payment $payment,
        $returnUrl = null,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null
    ): Authorization {
        $authorization = (new Authorization($amount, $currency, $returnUrl))->setOrderId($orderId);
        if ($card3ds !== null) {
            $authorization->setCard3ds($card3ds);
        }
        $payment->setAuthorization($authorization)->setCustomer($customer)->setMetadata($metadata)->setBasket($basket);
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
     * @param Metadata|null          $metadata         The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket           The Basket object corresponding to the payment.
     *                                                 The Basket object will be created automatically if it does not exist
     *                                                 yet (i.e. has no id).
     * @param bool|null              $card3ds          Enables 3ds channel for credit cards if available. This parameter is
     *                                                 optional and will be ignored if not applicable.
     * @param string|null            $invoiceId        The external id of the invoice.
     * @param string|null            $paymentReference A reference text for the payment.
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null,
        $invoiceId = null,
        $paymentReference = null
    ): AbstractTransactionType {
        $payment = $this->createPayment($paymentType);
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setOrderId($orderId)->setInvoiceId($invoiceId)->setPaymentReference($paymentReference);
        if ($card3ds !== null) {
            $charge->setCard3ds($card3ds);
        }
        $payment->addCharge($charge)->setCustomer($customer)->setMetadata($metadata)->setBasket($basket);
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
     * @throws RuntimeException
     */
    public function chargeAuthorization($payment, $amount = null): AbstractTransactionType
    {
        return $this->chargePayment($this->resourceService->getPaymentResource($payment), $amount);
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
     * @throws RuntimeException
     */
    public function chargePayment($payment, $amount = null, $currency = null): AbstractTransactionType
    {
        $charge = new Charge($amount, $currency);
        $charge->setPayment($payment);
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
     * @throws RuntimeException
     */
    public function cancelAuthorization(Authorization $authorization, $amount = null): AbstractTransactionType
    {
        $cancellation = new Cancellation($amount);
        $cancellation->setPayment($authorization->getPayment());
        $authorization->addCancellation($cancellation);
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
     * @throws RuntimeException
     */
    public function cancelAuthorizationByPayment($payment, $amount = null): AbstractTransactionType
    {
        $authorization = $this->resourceService->fetchAuthorization($payment);
        return $this->cancelAuthorization($authorization, $amount);
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
     * @throws RuntimeException
     */
    public function cancelChargeById($payment, $chargeId, $amount = null): AbstractTransactionType
    {
        $charge = $this->resourceService->fetchChargeById($payment, $chargeId);
        return $this->cancelCharge($charge, $amount);
    }

    /**
     * Create a Cancellation transaction for the given Charge resource.
     *
     * @param Charge $charge
     * @param $amount
     * @param string|null $reasonCode
     * @param string|null $paymentReference A reference string for the payment.
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function cancelCharge(
        Charge $charge,
        $amount = null,
        string $reasonCode = null,
        string $paymentReference = null
    ): AbstractTransactionType {
        $cancellation = new Cancellation($amount);
        $cancellation
            ->setReasonCode($reasonCode)
            ->setPayment($charge->getPayment())
            ->setPaymentReference($paymentReference);
        $charge->addCancellation($cancellation);
        $this->resourceService->create($cancellation);

        return $cancellation;
    }

    //</editor-fold>

    //<editor-fold desc="Shipment transaction">

    /**
     * Creates a Shipment transaction for the given Payment object.
     *
     * @param Payment|string $payment
     * @param string|null    $invoiceId
     *
     * @return Shipment Resulting Shipment object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function ship($payment, $invoiceId = null): AbstractHeidelpayResource
    {
        $shipment = new Shipment();
        $shipment->setInvoiceId($invoiceId);
        $this->resourceService->getPaymentResource($payment)->addShipment($shipment);
        $this->resourceService->create($shipment);
        return $shipment;
    }

    //</editor-fold>
    
    //</editor-fold>
}
