<?php
/**
 * This service provides for all methods to manage resources with the api.
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

use DateTime;
use Exception;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\IdStrings;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Alipay;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\EPS;
use heidelpayPHP\Resources\PaymentTypes\Giropay;
use heidelpayPHP\Resources\PaymentTypes\Ideal;
use heidelpayPHP\Resources\PaymentTypes\Invoice;
use heidelpayPHP\Resources\PaymentTypes\InvoiceFactoring;
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\PIS;
use heidelpayPHP\Resources\PaymentTypes\Prepayment;
use heidelpayPHP\Resources\PaymentTypes\Przelewy24;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\PaymentTypes\Wechatpay;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use function is_string;
use RuntimeException;
use stdClass;

class ResourceService
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

    //<editor-fold desc="General">

    /**
     * Send request to API.
     *
     * @param AbstractHeidelpayResource $resource
     * @param string                    $httpMethod
     *
     * @return stdClass
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function send(
        AbstractHeidelpayResource $resource,
        $httpMethod = HttpAdapterInterface::REQUEST_GET
    ): stdClass {
        $appendId     = $httpMethod !== HttpAdapterInterface::REQUEST_POST;
        $uri          = $resource->getUri($appendId);
        $responseJson = $resource->getHeidelpayObject()->getHttpService()->send($uri, $resource, $httpMethod);
        return json_decode($responseJson);
    }

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        if ($resource->getFetchedAt() === null && $resource->getId() !== null) {
            $this->fetch($resource);
        }
        return $resource;
    }

    /**
     * @param $url
     *
     * @return AbstractHeidelpayResource|null
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function fetchResourceByUrl($url)
    {
        $resource = null;
        $heidelpay    = $this->heidelpay;

        $resourceId   = IdService::getLastResourceIdFromUrlString($url);
        $resourceType = IdService::getResourceTypeFromIdString($resourceId);
        switch (true) {
            case $resourceType === IdStrings::AUTHORIZE:
                $resource = $heidelpay->fetchAuthorization(IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT));
                break;
            case $resourceType === IdStrings::CHARGE:
                $resource = $heidelpay->fetchChargeById(
                    IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT),
                    $resourceId
                );
                break;
            case $resourceType === IdStrings::SHIPMENT:
                $resource = $heidelpay->fetchShipment(
                    IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT),
                    $resourceId
                );
                break;
            case $resourceType === IdStrings::CANCEL:
                $paymentId  = IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT);
                $chargeId   = IdService::getResourceIdOrNullFromUrl($url, IdStrings::CHARGE);
                if ($chargeId !== null) {
                    $resource = $heidelpay->fetchRefundById($paymentId, $chargeId, $resourceId);
                    break;
                }
                $resource = $heidelpay->fetchReversal($paymentId, $resourceId);
                break;
            case $resourceType === IdStrings::PAYMENT:
                $resource = $heidelpay->fetchPayment($resourceId);
                break;
            case $resourceType === IdStrings::METADATA:
                $resource = $heidelpay->fetchMetadata($resourceId);
                break;
            case $resourceType === IdStrings::CUSTOMER:
                $resource = $heidelpay->fetchCustomer($resourceId);
                break;
            case $resourceType === IdStrings::BASKET:
                $resource = $heidelpay->fetchBasket($resourceId);
                break;
            case in_array($resourceType, IdStrings::PAYMENT_TYPES, true):
                $resource = $this->fetchPaymentType($resourceId);
                break;
            default:
                break;
        }

        return $resource;
    }

    //</editor-fold>

    //<editor-fold desc="CRUD operations">

    /**
     * Create the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function create(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        $method = HttpAdapterInterface::REQUEST_POST;
        $response = $this->send($resource, $method);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        if (isset($response->id)) {
            $resource->setId($response->id);
        }

        $resource->handleResponse($response, $method);
        return $resource;
    }

    /**
     * Update the resource on the api.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function update(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        $method = HttpAdapterInterface::REQUEST_PUT;
        $response = $this->send($resource, $method);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->handleResponse($response, $method);
        return $resource;
    }

    /**
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource|null
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function delete(AbstractHeidelpayResource &$resource)
    {
        $response = $this->send($resource, HttpAdapterInterface::REQUEST_DELETE);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $resource = null;

        return $resource;
    }

    /**
     * Fetch the resource from the api (id must be set).
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function fetch(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        $method = HttpAdapterInterface::REQUEST_GET;
        $response = $this->send($resource, $method);
        $resource->setFetchedAt(new DateTime('now'));
        $resource->handleResponse($response, $method);
        return $resource;
    }

    //</editor-fold>

    //<editor-fold desc="Payment resource">

    /**
     * Fetches the payment object if the id is given.
     * Else it just returns the given payment argument.
     * (!) It does not fetch or update a given payment object but returns it as-is. (!)
     *
     * @param $payment
     *
     * @return AbstractHeidelpayResource|Payment
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function getPaymentResource($payment)
    {
        $paymentObject = $payment;

        if (is_string($payment)) {
            $paymentObject = $this->fetchPayment($payment);
        }
        return $paymentObject;
    }

    /**
     * Fetch and return payment by given payment id.
     *
     * @param Payment|string $payment
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchPayment($payment): Payment
    {
        $paymentObject = $payment;
        if (is_string($payment)) {
            $paymentObject = new Payment($this->heidelpay);
            $paymentObject->setId($payment);
        }

        $this->fetch($paymentObject);
        return $paymentObject;
    }

    /**
     * Fetch and return payment by given order id.
     *
     * @param string $orderId
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchPaymentByOrderId($orderId): Payment
    {
        $paymentObject = (new Payment($this->heidelpay))->setOrderId($orderId);
        $this->fetch($paymentObject);
        return $paymentObject;
    }

    //</editor-fold>

    //<editor-fold desc="Keypair resource">

    /**
     * Fetch public key and configured payment types from API.
     *
     * @return Keypair
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchKeypair(): AbstractHeidelpayResource
    {
        $keyPair = (new Keypair())->setParentResource($this->heidelpay);
        return $this->fetch($keyPair);
    }

    //</editor-fold>

    //<editor-fold desc="Metadata resource">

    /**
     * Create Metadata resource.
     *
     * @param Metadata $metadata The Metadata object to be created.
     *
     * @return Metadata The fetched Metadata resource.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createMetadata(Metadata $metadata): Metadata
    {
        $metadata->setParentResource($this->heidelpay);
        $this->create($metadata);
        return $metadata;
    }

    /**
     * Fetch and return Metadata resource.
     *
     * @param Metadata|string $metadata
     *
     * @return Metadata
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchMetadata($metadata): Metadata
    {
        $metadataObject = $metadata;
        if (is_string($metadata)) {
            $metadataObject = (new Metadata())->setParentResource($this->heidelpay);
            $metadataObject->setId($metadata);
        }

        $this->fetch($metadataObject);
        return $metadataObject;
    }

    //</editor-fold>

    //<editor-fold desc="Basket resource">

    /**
     * Creates and returns the given basket resource.
     *
     * @param Basket $basket The basket to be created.
     *
     * @return Basket The created Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createBasket(Basket $basket): Basket
    {
        $basket->setParentResource($this->heidelpay);
        $this->create($basket);
        return $basket;
    }

    /**
     * Fetches and returns the given Basket (by object or id).
     *
     * @param Basket|string $basket Basket object or id of basket to be fetched.
     *
     * @return Basket The fetched Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchBasket($basket): Basket
    {
        $basketObj = $basket;
        if (is_string($basket)) {
            $basketObj = (new Basket())->setId($basket);
        }
        $basketObj->setParentResource($this->heidelpay);

        $this->fetch($basketObj);
        return $basketObj;
    }

    /**
     * Update the a basket resource with the given basket object (id must be set).
     *
     * @param Basket $basket
     *
     * @return Basket The updated Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function updateBasket(Basket $basket): Basket
    {
        $basket->setParentResource($this->heidelpay);
        $this->update($basket);
        return $basket;
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType resource">

    /**
     * Create the given payment type via api.
     *
     * @param BasePaymentType $paymentType
     *
     * @return BasePaymentType|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this->heidelpay);
        $this->create($paymentType);
        return $paymentType;
    }

    /**
     * Fetch the payment type with the given Id from the API.
     *
     * @param string $typeId
     *
     * @return BasePaymentType|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchPaymentType($typeId): AbstractHeidelpayResource
    {
        $resourceType = IdService::getResourceTypeFromIdString($typeId);
        switch ($resourceType) {
            case IdStrings::CARD:
                $paymentType = new Card(null, null);
                break;
            case IdStrings::GIROPAY:
                $paymentType = new Giropay();
                break;
            case IdStrings::IDEAL:
                $paymentType = new Ideal();
                break;
            case IdStrings::INVOICE:
                $paymentType = new Invoice();
                break;
            case IdStrings::INVOICE_GUARANTEED:
                $paymentType = new InvoiceGuaranteed();
                break;
            case IdStrings::PAYPAL:
                $paymentType = new Paypal();
                break;
            case IdStrings::PREPAYMENT:
                $paymentType = new Prepayment();
                break;
            case IdStrings::PRZELEWY24:
                $paymentType = new Przelewy24();
                break;
            case IdStrings::SEPA_DIRECT_DEBIT_GUARANTEED:
                $paymentType = new SepaDirectDebitGuaranteed(null);
                break;
            case IdStrings::SEPA_DIRECT_DEBIT:
                $paymentType = new SepaDirectDebit(null);
                break;
            case IdStrings::SOFORT:
                $paymentType = new Sofort();
                break;
            case IdStrings::PIS:
                $paymentType = new PIS();
                break;
            case IdStrings::EPS:
                $paymentType = new EPS();
                break;
            case IdStrings::ALIPAY:
                $paymentType = new Alipay();
                break;
            case IdStrings::WECHATPAY:
                $paymentType = new Wechatpay();
                break;
            case IdStrings::INVOICE_FACTORING:
                $paymentType = new InvoiceFactoring();
                break;
            default:
                throw new RuntimeException('Invalid payment type!');
                break;
        }

        return $this->fetch($paymentType->setParentResource($this->heidelpay)->setId($typeId));
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
     * @throws RuntimeException
     */
    public function createCustomer(Customer $customer): AbstractHeidelpayResource
    {
        $customer->setParentResource($this->heidelpay);
        $this->create($customer);
        return $customer;
    }

    /**
     * Create the given Customer object via API.
     *
     * @param Customer $customer
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createOrUpdateCustomer(Customer $customer): AbstractHeidelpayResource
    {
        try {
            $this->createCustomer($customer);
        } catch (HeidelpayApiException $e) {
            if (ApiResponseCodes::API_ERROR_CUSTOMER_ID_ALREADY_EXISTS !== $e->getCode()) {
                throw $e;
            }

            // fetch Customer resource by customerId
            $fetchedCustomer = $this->fetchCustomer((new Customer())->setCustomerId($customer->getCustomerId()));

            // update the existing customer with the data of the new customer
            $this->updateCustomer($customer->setId($fetchedCustomer->getId()));
        }

        return $customer;
    }

    /**
     * Fetch and return Customer object from API by the given id.
     *
     * @param Customer|string $customer
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchCustomer($customer): AbstractHeidelpayResource
    {
        $customerObject = $customer;

        if (is_string($customer)) {
            $customerObject = (new Customer())->setId($customer);
        }

        $this->fetch($customerObject->setParentResource($this->heidelpay));
        return $customerObject;
    }

    /**
     * Fetch and return Customer object from API by the given external customer id.
     *
     * @param string $customerId
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchCustomerByExtCustomerId($customerId): Customer
    {
        $customerObject = (new Customer())->setCustomerId($customerId);
        $this->fetch($customerObject->setParentResource($this->heidelpay));
        return $customerObject;
    }

    /**
     * Update and return a Customer object via API.
     *
     * @param Customer $customer
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function updateCustomer(Customer $customer): AbstractHeidelpayResource
    {
        $this->update($customer);
        return $customer;
    }

    /**
     * Delete the given Customer resource.
     *
     * @param Customer|string|null $customer
     *
     * @return Customer|null|string
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function deleteCustomer($customer)
    {
        $customerObject = $customer;

        if (is_string($customer)) {
            $customerObject = $this->fetchCustomer($customer);
        }

        $this->delete($customerObject);
        return $customerObject;
    }

    //</editor-fold>

    //<editor-fold desc="Authorization resource">

    /**
     * Fetch an Authorization object by its paymentId.
     * Authorization Ids are not global but specific to the payment.
     * A Payment object can have zero to one authorizations.
     *
     * @param $payment
     *
     * @return Authorization
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchAuthorization($payment): AbstractHeidelpayResource
    {
        /** @var Payment $paymentObject */
        $paymentObject = $this->fetchPayment($payment);
        return $this->fetch($paymentObject->getAuthorization(true));
    }

    //</editor-fold>

    //<editor-fold desc="Charge resource">

    /**
     * Fetch a Charge object by paymentId and chargeId.
     * Charge Ids are not global but specific to the payment.
     *
     * @param Payment|string $payment
     * @param string         $chargeId
     *
     * @return Charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchChargeById($payment, $chargeId): AbstractHeidelpayResource
    {
        /** @var Payment $paymentObject */
        $paymentObject = $this->fetchPayment($payment);
        return $this->fetch($paymentObject->getCharge($chargeId, true));
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
     * @throws RuntimeException
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): AbstractHeidelpayResource
    {
        $this->fetch($authorization);
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
     * @throws RuntimeException
     */
    public function fetchReversal($paymentId, $cancellationId): AbstractHeidelpayResource
    {
        /** @var Authorization $authorization */
        $authorization = $this->fetchPayment($paymentId)->getAuthorization();
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * Fetch a Cancellation resource on a charge (aka refund) via id.
     *
     * @param Payment|string $payment
     * @param string         $chargeId
     * @param string         $cancellationId
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchRefundById($payment, $chargeId, $cancellationId): AbstractHeidelpayResource
    {
        /** @var Charge $charge */
        $charge = $this->fetchChargeById($payment, $chargeId);
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
     * @throws RuntimeException
     */
    public function fetchRefund(Charge $charge, $cancellationId): AbstractHeidelpayResource
    {
        return $this->fetch($charge->getCancellation($cancellationId, true));
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
     * @throws RuntimeException
     */
    public function fetchShipment($payment, $shipmentId): AbstractHeidelpayResource
    {
        $paymentObject = $this->fetchPayment($payment);
        return $paymentObject->getShipment($shipmentId);
    }

    //</editor-fold>
}
