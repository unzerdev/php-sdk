<?php
/**
 * This is the heidelpay object which is the base object providing all functionalities needed to
 * access the api.
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
 * @package  heidelpay/mgw_sdk
 */
namespace heidelpay\MgwPhpSdk;

use heidelpay\MgwPhpSdk\Adapter\CurlAdapter;
use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\SupportedLocale;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\IllegalResourceTypeException;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\MgwPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\GiroPay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;
use heidelpay\MgwPhpSdk\Services\ResourceService;

class Heidelpay implements HeidelpayParentInterface
{
    const BASE_URL = 'https://api.heidelpay.com/';
    const API_VERSION = 'v1';
    const SDK_VERSION = 'HeidelpayPHP 1.0.0-beta';
    const DEBUG_MODE = true;

    /**
     * @param string $key
     * @param string $locale
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN)
    {
        $this->setKey($key);
        $this->locale = $locale;

        $this->resourceService = new ResourceService();
    }

    /**
     * @param $uri
     * @param HeidelpayResourceInterface $resource
     * @param string                     $method
     *
     * @return string
     */
    public function send(
        $uri,
        HeidelpayResourceInterface $resource,
        $method = HttpAdapterInterface::REQUEST_GET
    ): string {
        if (!$this->adapter instanceof HttpAdapterInterface) {
            $this->adapter = new CurlAdapter();
        }
        return $this->adapter->send(self::BASE_URL . self::API_VERSION . $uri, $resource, $method);
    }

    //<editor-fold desc="Properties">
    /** @var string $key */
    private $key;

    /** @var string $locale */
    private $locale;

    /** @var HttpAdapterInterface $adapter */
    private $adapter;

    /** @var ResourceService $resourceService */
    private $resourceService;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Heidelpay
     */
    public function setKey($key): Heidelpay
    {
        $isPrivateKey = strpos($key, 's-priv-') !== false;
        if (!$isPrivateKey) {
            throw new IllegalKeyException();
        }

        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return Heidelpay
     */
    public function setLocale($locale): Heidelpay
    {
        $this->locale = $locale;
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
     * @return Heidelpay
     */
    public function setResourceService(ResourceService $resourceService): Heidelpay
    {
        $this->resourceService = $resourceService;
        return $this;
    }

    //<editor-fold desc="ParentIF">

    /**
     * Returns the heidelpay root object.
     *
     * @return Heidelpay
     */
    public function getHeidelpayObject(): Heidelpay
    {
        return $this;
    }

    /**
     * Returns the url string for this resource.
     *
     * @return string
     */
    public function getUri(): string
    {
        return '';
    }

    //</editor-fold>

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Resources">
    //<editor-fold desc="Payment resource">

    /**
     * @param PaymentTypeInterface|string $paymentType
     * @param Customer|string|null        $customer
     *
     * @return Payment
     */
    private function createPayment($paymentType, $customer = null): HeidelpayResourceInterface
    {
        return (new Payment($this))->setPaymentType($paymentType)->setCustomer($customer);
    }

    /**
     * Fetch and return payment by given payment id.
     *
     * @param string $paymentId
     *
     * @return Payment
     */
    public function fetchPaymentById($paymentId): HeidelpayResourceInterface
    {
        $payment = new Payment($this);
        $payment->setId($paymentId);
        return $this->fetchPayment($payment);
    }

    /**
     * Fetch and return payment by given payment id.
     *
     * @param Payment $payment
     *
     * @return Payment
     */
    public function fetchPayment(Payment $payment): HeidelpayResourceInterface
    {
        $this->resourceService->fetch($payment);
        if (!$payment instanceof Payment) {
            throw new IllegalResourceTypeException(Payment::class, \get_class($payment));
        }
        return $payment;
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType resource">

    /**
     * Create the given payment type via api.
     *
     * @param PaymentTypeInterface $paymentType
     *
     * @return PaymentTypeInterface
     */
    public function createPaymentType(PaymentTypeInterface $paymentType): HeidelpayResourceInterface
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this);
        return $this->resourceService->create($paymentType);
    }

    /**
     * @param string $typeId
     *
     * @return PaymentTypeInterface
     */
    public function fetchPaymentType($typeId): HeidelpayResourceInterface
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3})/', $typeId, $typeIdParts);

        // todo maybe move this into a builder service
        switch ($typeIdParts[1]) {
            case 'crd':
                $paymentType = new Card(null, null);
                break;
            case 'gro':
                $paymentType = new GiroPay();
                break;
            case 'idl':
                $paymentType = new Ideal();
                break;
            case 'ivc':
                $paymentType = new Invoice();
                break;
            default:
                throw new IllegalTransactionTypeException($typeId);
                break;
        }

        return $this->resourceService->fetch($paymentType->setParentResource($this)->setId($typeId));
    }

    //</editor-fold>

    //<editor-fold desc="Customer resource">

    /**
     * Create the given Customer object via API.
     *
     * @param Customer $customer
     *
     * @return Customer
     */
    public function createCustomer(Customer $customer): HeidelpayResourceInterface
    {
        $customer->setParentResource($this);
        return $this->resourceService->create($customer);
    }

    /**
     * Fetch and return Customer object from API by the given id.
     *
     * @param Customer|string $customer
     *
     * @return Customer
     */
    public function fetchCustomer($customer): HeidelpayResourceInterface
    {
        $customerObject = $customer;

        if (\is_string($customer)) {
            $customerObject = (new Customer())->setParentResource($this)->setId($customer);
        }

        return $this->resourceService->fetch($customerObject);
    }

    /**
     * Update and return a Customer object via API.
     *
     * @param Customer $customer
     *
     * @return Customer
     */
    public function updateCustomer(Customer $customer): HeidelpayResourceInterface
    {
        return $this->resourceService->update($customer);
    }

    /**
     * Delete a Customer object via API.
     *
     * @param string $customerId
     *
     * @throws HeidelpayApiException
     */
    public function deleteCustomerById($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->fetchCustomer($customerId);
        $this->deleteCustomer($customer);
    }

    /**
     * @param Customer $customer
     *
     * @throws HeidelpayApiException
     */
    public function deleteCustomer(Customer $customer)
    {
        $this->getResourceService()->delete($customer);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization resource">

    /**
     * Fetch an Authorization object by its paymentId.
     * Authorization Ids are not global but specific to the payment.
     * A Payment object can have zero to one authorizations.
     *
     * @param $paymentId
     *
     * @return Authorization
     */
    public function fetchAuthorization($paymentId): HeidelpayResourceInterface
    {
        /** @var Payment $payment */
        $payment = $this->fetchPaymentById($paymentId);
        $authorization = $this->getResourceService()->fetch($payment->getAuthorization(true));
        return $authorization;
    }

    //</editor-fold>

    //<editor-fold desc="Charge resource">

    /**
     * Fetch a Charge object by paymentId and chargeId.
     * Charge Ids are not global but specific to the payment.
     *
     * @param string $paymentId
     * @param string $chargeId
     *
     * @return Charge
     */
    public function fetchChargeById($paymentId, $chargeId): HeidelpayResourceInterface
    {
        /** @var Payment $payment */
        $payment = $this->fetchPaymentById($paymentId);
        return $this->getResourceService()->fetch($payment->getChargeById($chargeId, true));
    }

    /**
     * Fetch a Charge object by.
     *
     * @param Charge $charge
     *
     * @return Charge
     */
    public function fetchCharge(Charge $charge): HeidelpayResourceInterface
    {
        return $this->getResourceService()->fetch($charge);
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation resource">

    /**
     * Fetch a cancel on an authorization (aka reversal).
     *
     * @param Authorization|string $authorization
     * @param string               $cancellationId
     *
     * @return Cancellation
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): HeidelpayResourceInterface
    {
        $this->getResourceService()->fetch($authorization);
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * Fetch a cancel on an authorization (aka reversal).
     *
     * @param string $paymentId
     * @param string $cancellationId
     *
     * @return Cancellation
     */
    public function fetchReversal($paymentId, $cancellationId): HeidelpayResourceInterface
    {
        /** @var Authorization $authorization */
        $authorization = $this->fetchPaymentById($paymentId)->getAuthorization();
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * Fetch a cancel on an authorization (aka reversal).
     *
     * @param string $paymentId
     * @param string $chargeId
     * @param string $cancellationId
     *
     * @return Cancellation
     */
    public function fetchRefundById($paymentId, $chargeId, $cancellationId): HeidelpayResourceInterface
    {
        /** @var Charge $charge */
        $charge = $this->fetchChargeById($paymentId, $chargeId);
        return $this->fetchRefund($charge, $cancellationId);
    }

    /**
     * Fetch a cancel on an authorization (aka reversal).
     *
     * @param Charge $charge
     * @param string $cancellationId
     *
     * @return Cancellation
     */
    public function fetchRefund(Charge $charge, $cancellationId): HeidelpayResourceInterface
    {
        return $this->getResourceService()->fetch($charge->getCancellation($cancellationId, true));
    }

    //</editor-fold>

    //<editor-fold desc="Shipment resource">

    /**
     * Fetch a shipment on a payment.
     *
     * @param Payment|string $payment
     * @param string               $shipmentId
     *
     * @return Shipment
     */
    public function fetchShipmentByPayment($payment, $shipmentId): HeidelpayResourceInterface
    {
        $this->getResourceService()->fetch($payment);
        return $payment->getShipmentById($shipmentId);
    }

    /**
     * Fetch a shipment on an payment.
     *
     * @param string $paymentId
     * @param string $shipmentId
     *
     * @return Shipment
     */
    public function fetchShipment($paymentId, $shipmentId): HeidelpayResourceInterface
    {
        $payment = $this->fetchPaymentById($paymentId);
        return $payment->getShipmentById($shipmentId);
    }
    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Transactions">
    //<editor-fold desc="Authorize transactions">

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentTypeId
     * @param string        $returnUrl
     * @param Customer|null $customer
     *
     * @return Authorization
     */
    public function authorizeWithPaymentTypeId(
        $amount,
        $currency,
        $paymentTypeId,
        $returnUrl,
        $customer = null
    ): AbstractTransactionType {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->authorize($amount, $currency, $paymentType, $returnUrl, $customer);
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param $amount
     * @param $currency
     * @param Payment $payment
     * @param $returnUrl
     * @param null $customer
     *
     * @return Authorization
     */
    public function authorizeWithPayment(
        $amount,
        $currency,
        Payment $payment,
        $returnUrl = null,
        $customer = null
    ): AbstractTransactionType {
        $authorization = new Authorization($amount, $currency, $returnUrl);
        $payment->setAuthorization($authorization);
        $payment->setCustomer($customer);
        $this->resourceService->create($authorization);
        return $authorization;
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentType
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     *
     * @return Authorization
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl, $customer = null): AbstractTransactionType
    {
        $payment = $this->createPayment($paymentType, $customer);
        return $this->authorizeWithPayment($amount, $currency, $payment, $returnUrl, $customer);
    }

    //</editor-fold>

    //<editor-fold desc="Charge transactions">

    /**
     * Performs a charge and returns the corresponding Charge object.
     *
     * @param float         $amount
     * @param string        $currency
     * @param string        $returnUrl
     * @param string        $paymentTypeId
     * @param Customer|null $customer
     *
     * @return Charge
     */
    public function chargeWithPaymentTypeId(
        $amount,
        $currency,
        $paymentTypeId,
        $returnUrl,
        $customer = null
    ): AbstractTransactionType {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->charge($amount, $currency, $paymentType, $returnUrl, $customer);
    }

    /**
     * @param PaymentTypeInterface|string $paymentType
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param Customer|string|null $customer
     *
     * @return Charge
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null): AbstractTransactionType
    {
        $payment = $this->createPayment($paymentType, $customer);
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setParentResource($payment)->setPayment($payment);
        $payment->addCharge($charge);
        $this->resourceService->create($charge);

        return $charge;
    }

    /**
     * @param $paymentId
     * @param null $amount
     *
     * @return Charge
     */
    public function chargeAuthorization($paymentId, $amount = null): AbstractTransactionType
    {
        $payment = $this->fetchPaymentById($paymentId);
        return $this->chargePayment($payment, $amount);
    }

    /**
     * @param Payment $payment
     * @param null    $amount
     * @param null    $currency
     *
     * @return Charge
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

    //<editor-fold desc="Cancellation/Reversal">

    /**
     * Creates a cancellation for the authorization of the given payment.
     *
     * @param Authorization $authorization
     * @param null          $amount
     *
     * @return Cancellation
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
     * Creates a cancellation for the given Authorization object.
     *
     * @param string $paymentId
     * @param null   $amount
     *
     * @return Cancellation
     */
    public function cancelAuthorizationByPaymentId($paymentId, $amount = null): AbstractTransactionType
    {
        return $this->cancelAuthorization($this->fetchAuthorization($paymentId), $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation/Refund">

    /**
     * Create a cancellation for the charge with the given id belonging to the given Payment object.
     *
     * @param string $paymentId
     * @param string $chargeId
     * @param null   $amount
     *
     * @return Cancellation
     */
    public function cancelChargeById($paymentId, $chargeId, $amount = null): AbstractTransactionType
    {
        return $this->cancelCharge($this->fetchChargeById($paymentId, $chargeId), $amount);
    }

    /**
     * Create a cancellation for the given charge.
     *
     * @param $amount
     * @param Charge $charge
     *
     * @return Cancellation
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

    //<editor-fold desc="Shipment transactions">
    /**
     * Creates a shipment transaction for the given payment object.
     *
     * @param Payment|string $payment
     *
     * @return Shipment
     */
    public function ship($payment): HeidelpayResourceInterface
    {
        $shipment = new Shipment();
        $payment->addShipment($shipment);
        return $this->getResourceService()->create($shipment);
    }
    //</editor-fold>
    //</editor-fold>
}
