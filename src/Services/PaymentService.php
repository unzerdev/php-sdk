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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Services
 */
namespace heidelpayPHP\Services;

use DateTime;
use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\PaymentServiceInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\InstalmentPlans;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use RuntimeException;

class PaymentService implements PaymentServiceInterface
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
        $this->heidelpay       = $heidelpay;
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
        return $this->getHeidelpay()->getResourceService();
    }

    //</editor-fold>

    //<editor-fold desc="Transactions">

    //<editor-fold desc="Authorize transaction">

    /**
     * {@inheritDoc}
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
        $card3ds = null,
        $invoiceId = null,
        $referenceText = null
    ): Authorization {
        $payment = $this->createPayment($paymentType);
        $paymentType = $payment->getPaymentType();

        /** @var Authorization $authorization */
        $authorization = (new Authorization($amount, $currency, $returnUrl))
            ->setOrderId($orderId)
            ->setInvoiceId($invoiceId)
            ->setPaymentReference($referenceText)
            ->setSpecialParams($paymentType !== null ? $paymentType->getTransactionParams() : []);
        if ($card3ds !== null) {
            $authorization->setCard3ds($card3ds);
        }
        $payment->setAuthorization($authorization)->setCustomer($customer)->setMetadata($metadata)->setBasket($basket);
        $this->getResourceService()->createResource($authorization);
        return $authorization;
    }

    //</editor-fold>

    //<editor-fold desc="Charge transaction">

    /**
     * {@inheritDoc}
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
    ): Charge {
        $payment     = $this->createPayment($paymentType);
        $paymentType = $payment->getPaymentType();

        /** @var Charge $charge */
        $charge = (new Charge($amount, $currency, $returnUrl))
            ->setOrderId($orderId)
            ->setInvoiceId($invoiceId)
            ->setPaymentReference($paymentReference)
            ->setSpecialParams($paymentType->getTransactionParams() ?? []);
        if ($card3ds !== null) {
            $charge->setCard3ds($card3ds);
        }
        $payment->addCharge($charge)->setCustomer($customer)->setMetadata($metadata)->setBasket($basket);
        $this->getResourceService()->createResource($charge);

        return $charge;
    }

    /**
     * {@inheritDoc}
     */
    public function chargeAuthorization(
        $payment,
        float $amount = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge {
        $paymentResource = $this->getResourceService()->getPaymentResource($payment);
        return $this->chargePayment($paymentResource, $amount, $orderId, $invoiceId);
    }

    /**
     * {@inheritDoc}
     */
    public function chargePayment(
        $payment,
        float $amount = null,
        string $currency = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge {
        $charge = new Charge($amount, $currency);
        $charge->setPayment($payment);
        if ($orderId !== null) {
            $charge->setOrderId($orderId);
        }
        if ($invoiceId !== null) {
            $charge->setInvoiceId($invoiceId);
        }
        $payment->addCharge($charge);
        $this->getResourceService()->createResource($charge);
        return $charge;
    }

    //</editor-fold>

    //<editor-fold desc="Payout transactions">

    /**
     * {@inheritDoc}
     */
    public function payout(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $invoiceId = null,
        $referenceText = null
    ): Payout {
        $payment = $this->createPayment($paymentType);
        $payout = (new Payout($amount, $currency, $returnUrl))
            ->setOrderId($orderId)
            ->setInvoiceId($invoiceId)
            ->setPaymentReference($referenceText);
        $payment->setPayout($payout)->setCustomer($customer)->setMetadata($metadata)->setBasket($basket);
        $this->getResourceService()->createResource($payout);

        return $payout;
    }

    //</editor-fold>

    //<editor-fold desc="Shipment transaction">

    /**
     * {@inheritDoc}
     */
    public function ship($payment, string $invoiceId = null, string $orderId = null): Shipment
    {
        $shipment = new Shipment();
        $shipment->setInvoiceId($invoiceId)->setOrderId($orderId);
        $this->getResourceService()->getPaymentResource($payment)->addShipment($shipment);
        $this->getResourceService()->createResource($shipment);
        return $shipment;
    }

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Paypage">

    /**
     * {@inheritDoc}
     */
    public function initPayPageCharge(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->initPayPage($paypage, TransactionTypes::CHARGE, $customer, $basket, $metadata);
    }

    /**
     * {@inheritDoc}
     */
    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->initPayPage($paypage, TransactionTypes::AUTHORIZATION, $customer, $basket, $metadata);
    }

    //</editor-fold>

    //<editor-fold desc="Hire Purchase (FlexiPay Rate)">

    /**
     * {@inheritDoc}
     */
    public function fetchDirectDebitInstalmentPlans(
        $amount,
        $currency,
        $effectiveInterest,
        DateTime $orderDate = null
    ): InstalmentPlans {
        $hdd   = (new HirePurchaseDirectDebit(null, null, null))->setParentResource($this->heidelpay);
        /** @var InstalmentPlans $plans */
        $plans = (new InstalmentPlans($amount, $currency, $effectiveInterest, $orderDate))->setParentResource($hdd);
        $plans = $this->heidelpay->getResourceService()->fetchResource($plans);
        return $plans;
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Creates the PayPage for the requested transaction method.
     *
     * @param Paypage              $paypage  The PayPage resource to initialize.
     * @param string               $action   The transaction type (Charge or Authorize) to create the PayPage for.
     *                                       Depending on the chosen transaction the payment types available will vary.
     * @param Customer|string|null $customer The optional customer object.
     *                                       Keep in mind that payment types with mandatory customer object might not be
     *                                       available to the customer if no customer resource is referenced here.
     * @param Basket|null          $basket   The optional Basket object.
     *                                       Keep in mind that payment types with mandatory basket object might not be
     *                                       available to the customer if no basket resource is referenced here.
     * @param Metadata|null        $metadata The optional metadata resource.
     *
     * @return Paypage The updated PayPage resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function initPayPage(
        Paypage $paypage,
        $action,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        $paypage->setAction($action)->setParentResource($this->heidelpay);
        $payment = $this->createPayment($paypage)->setBasket($basket)->setCustomer($customer)->setMetadata($metadata);
        $this->getResourceService()->createResource($paypage->setPayment($payment));
        return $paypage;
    }

    /**
     * Create a Payment object with the given properties.
     *
     * @param BasePaymentType|string $paymentType
     *
     * @return Payment The resulting Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function createPayment($paymentType): AbstractHeidelpayResource
    {
        return (new Payment($this->heidelpay))->setPaymentType($paymentType);
    }

    //</editor-fold>
}
