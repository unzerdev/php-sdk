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
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Prepayment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebit;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\AbstractTransactionType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;
use heidelpay\MgwPhpSdk\Services\ResourceService;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;

class Heidelpay implements HeidelpayParentInterface
{
    const BASE_URL = 'https://api.heidelpay.com/';
    const API_VERSION = 'v1';
    const SDK_VERSION = 'HeidelpayPHP 1.0.0-beta';
    const DEBUG_MODE = true;

    /**
     * Construct a new heidelpay object.
     *
     * @param string $key
     * @param string $locale
     *
     * @throws HeidelpaySdkException Will be thrown if the key is not of type private.
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN)
    {
        $this->setKey($key);
        $this->locale = $locale;

        $this->resourceService = new ResourceService();
    }

    //<editor-fold desc="Helpers">

    /**
     * Send the given resource object to the given url using the specified Http method (default = GET).
     *
     * @param string                     $uri      The URI to send the request to.
     * @param HeidelpayResourceInterface $resource The resource to be send.
     * @param string                     $method   The Http method to be used.
     *
     * @return string The response as a JSON string.
     *
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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

    /**
     * Returns true if the given key has a valid format.
     *
     * @param $key
     *
     * @return bool
     */
    public function isValidKey($key): bool
    {
        $match = [];
        preg_match('/^[sp]{1}-(priv)-[a-zA-Z0-9]+/', $key, $match);
        return !(\count($match) < 2 || $match[1] !== 'priv');
    }

    //</editor-fold>

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
     * Set the API key authenticate to the api.
     *
     * @param string $key
     *
     * @return Heidelpay
     *
     * @throws HeidelpaySdkException
     */
    public function setKey($key): Heidelpay
    {
        if (!$this->isValidKey($key)) {
            throw new HeidelpaySdkException('Illegal key type: Use the private key with this SDK!');
        }

        $this->key = $key;
        return $this;
    }

    /**
     * Return the language locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the language locale.
     *
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
     * Return the ResourceService object.
     *
     * @return ResourceService
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * Set the ResourceService object.
     *
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
     * Return the heidelpay root object.
     *
     * @return Heidelpay
     */
    public function getHeidelpayObject(): Heidelpay
    {
        return $this;
    }

    /**
     * Return the url string for this resource.
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
     * Create a Payment object with the given properties.
     *
     * @param PaymentTypeInterface|string $paymentType
     * @param Customer|string|null        $customer
     *
     * @return Payment The resulting Payment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     *
     * @throws HeidelpayApiException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function fetchPayment(Payment $payment): HeidelpayResourceInterface
    {
        $this->resourceService->fetch($payment);
        if (!$payment instanceof Payment) {
            throw new HeidelpaySdkException(
                sprintf('Resource type %s is not allowed, type %s expected!', Payment::class, \get_class($payment))
            );
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
     * @return PaymentTypeInterface|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function createPaymentType(PaymentTypeInterface $paymentType): HeidelpayResourceInterface
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this);
        return $this->resourceService->create($paymentType);
    }

    /**
     * Fetch the payment type with the given Id from the API.
     *
     * @param string $typeId
     *
     * @return PaymentTypeInterface|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchPaymentType($typeId): HeidelpayResourceInterface
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3}|p24)-[a-z0-9]*/', $typeId, $typeIdParts);

        // todo maybe move this into a builder service
        switch ($typeIdParts[1]) {
            case 'crd':
                $paymentType = new Card(null, null);
                break;
            case 'gro':
                $paymentType = new Giropay();
                break;
            case 'idl':
                $paymentType = new Ideal();
                break;
            case 'ivc':
                $paymentType = new Invoice();
                break;
            case 'ivg':
                $paymentType = new InvoiceGuaranteed();
                break;
            case 'ppl':
                $paymentType = new Paypal();
                break;
            case 'ppy':
                $paymentType = new Prepayment();
                break;
            case 'p24':
                $paymentType = new Przelewy24();
                break;
            case 'ddg':
                $paymentType = new SepaDirectDebitGuaranteed(null);
                break;
            case 'sdd':
                $paymentType = new SepaDirectDebit(null);
                break;
            case 'sft':
                $paymentType = new Sofort();
                break;
            default:
                throw new HeidelpaySdkException(sprintf('Payment type "%s" is not allowed!', $typeIdParts[1]));
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
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
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
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function deleteCustomerById($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->fetchCustomer($customerId);
        $this->deleteCustomer($customer);
    }

    /**
     * Delete the given Customer resource.
     *
     * @param Customer $customer
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     *
     * todo: allows customer id as well
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
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
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
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchChargeById($paymentId, $chargeId): HeidelpayResourceInterface
    {
        /** @var Payment $payment */
        $payment = $this->fetchPaymentById($paymentId);
        return $this->getResourceService()->fetch($payment->getChargeById($chargeId, true));
    }

    /**
     * Fetch the given Charge resource from the api.
     *
     * @param Charge $charge
     *
     * @return Charge
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
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
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): HeidelpayResourceInterface
    {
        $this->getResourceService()->fetch($authorization);
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * Fetch a Cancellation resource on an authorization (aka reversal) via id.
     *
     * @param string $paymentId
     * @param string $cancellationId
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchReversal($paymentId, $cancellationId): HeidelpayResourceInterface
    {
        /** @var Authorization $authorization */
        $authorization = $this->fetchPaymentById($paymentId)->getAuthorization();
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * Fetch a Cancellation resource on a charge (aka refund) via id.
     *
     * @param string $paymentId
     * @param string $chargeId
     * @param string $cancellationId
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchRefundById($paymentId, $chargeId, $cancellationId): HeidelpayResourceInterface
    {
        /** @var Charge $charge */
        $charge = $this->fetchChargeById($paymentId, $chargeId);
        return $this->fetchRefund($charge, $cancellationId);
    }

    /**
     * Fetch a Cancellation resource on a Charge (aka refund).
     *
     * @param Charge $charge
     * @param string $cancellationId
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchRefund(Charge $charge, $cancellationId): HeidelpayResourceInterface
    {
        return $this->getResourceService()->fetch($charge->getCancellation($cancellationId, true));
    }

    //</editor-fold>

    //<editor-fold desc="Shipment resource">

    /**
     * Fetch a Shipment resource of the given Payment resource by id.
     *
     * @param Payment|string $payment
     * @param string         $shipmentId
     *
     * @return Shipment
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function fetchShipmentByPayment($payment, $shipmentId): HeidelpayResourceInterface
    {
        $this->getResourceService()->fetch($payment);
        return $payment->getShipmentById($shipmentId);
    }

    /**
     * Fetch a Shipment resource of the given Payment resource by id.
     *
     * @param string $paymentId
     * @param string $shipmentId
     *
     * @return Shipment
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     * Perform an Authorization transaction and return the corresponding Authorization object.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentTypeId
     * @param string        $returnUrl
     * @param Customer|null $customer
     * @param string|null   $orderId
     *
     * @return Authorization Resulting Authorization object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function authorizeWithPaymentTypeId(
        $amount,
        $currency,
        $paymentTypeId,
        $returnUrl,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->authorize($amount, $currency, $paymentType, $returnUrl, $customer, $orderId);
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
     * @throws HeidelpaySdkException
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
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl, $customer = null, $orderId = null): AbstractTransactionType
    {
        $payment = $this->createPayment($paymentType, $customer);
        return $this->authorizeWithPayment($amount, $currency, $payment, $returnUrl, $customer, $orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Charge transactions">

    /**
     * Perform a Charge transaction and return the corresponding Charge object.
     *
     * @param float         $amount
     * @param string        $currency
     * @param string        $paymentTypeId
     * @param string        $returnUrl
     * @param Customer|null $customer
     * @param string|null   $orderId
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function chargeWithPaymentTypeId(
        $amount,
        $currency,
        $paymentTypeId,
        $returnUrl,
        $customer = null,
        $orderId = null
    ): AbstractTransactionType {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->charge($amount, $currency, $paymentType, $returnUrl, $customer, $orderId);
    }

    /**
     * Charge the given amount and currency on the given PaymentType resource.
     *
     * @param float                       $amount
     * @param string                      $currency
     * @param PaymentTypeInterface|string $paymentType
     * @param string                      $returnUrl
     * @param Customer|string|null        $customer
     * @param string|null                 $orderId
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     * @param $paymentId
     * @param null $amount
     *
     * @return Charge Resulting Charge object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function chargeAuthorization($paymentId, $amount = null): AbstractTransactionType
    {
        $payment = $this->fetchPaymentById($paymentId);
        return $this->chargePayment($payment, $amount);
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
     * @throws HeidelpaySdkException
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
     * Perform a Cancellation transaction with the given amount for the given Authorization.
     *
     * @param Authorization $authorization
     * @param null          $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
     * @param string $paymentId
     * @param null   $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function cancelAuthorizationByPaymentId($paymentId, $amount = null): AbstractTransactionType
    {
        return $this->cancelAuthorization($this->fetchAuthorization($paymentId), $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation/Refund">

    /**
     * Create a Cancellation transaction for the charge with the given id belonging to the given Payment object.
     *
     * @param string $paymentId
     * @param string $chargeId
     * @param null   $amount
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function cancelChargeById($paymentId, $chargeId, $amount = null): AbstractTransactionType
    {
        return $this->cancelCharge($this->fetchChargeById($paymentId, $chargeId), $amount);
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
     * @throws HeidelpaySdkException
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
     * Creates a Shipment transaction for the given Payment object.
     *
     * @param Payment|string $payment
     *
     * @return Shipment Resulting Shipment object.
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
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
