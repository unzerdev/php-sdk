<?php
/**
 * This service provides for all methods to manage resources with the api.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\Services
 */
namespace UnzerSDK\Services;

use DateTime;
use Exception;
use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Constants\IdStrings;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\Applepay;
use UnzerSDK\Unzer;
use UnzerSDK\Interfaces\ResourceServiceInterface;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Keypair;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\Alipay;
use UnzerSDK\Resources\PaymentTypes\Bancontact;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\PaymentTypes\EPS;
use UnzerSDK\Resources\PaymentTypes\Giropay;
use UnzerSDK\Resources\PaymentTypes\InstallmentSecured;
use UnzerSDK\Resources\PaymentTypes\Ideal;
use UnzerSDK\Resources\PaymentTypes\Invoice;
use UnzerSDK\Resources\PaymentTypes\InvoiceSecured;
use UnzerSDK\Resources\PaymentTypes\Paypal;
use UnzerSDK\Resources\PaymentTypes\PIS;
use UnzerSDK\Resources\PaymentTypes\Prepayment;
use UnzerSDK\Resources\PaymentTypes\Przelewy24;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\Resources\PaymentTypes\SepaDirectDebitSecured;
use UnzerSDK\Resources\PaymentTypes\Sofort;
use UnzerSDK\Resources\PaymentTypes\Wechatpay;
use UnzerSDK\Resources\Recurring;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\Traits\CanRecur;
use RuntimeException;
use stdClass;
use function in_array;
use function is_string;

class ResourceService implements ResourceServiceInterface
{
    /** @var Unzer */
    private $unzer;

    /**
     * ResourceService constructor.
     *
     * @param Unzer $unzer
     */
    public function __construct(Unzer $unzer)
    {
        $this->unzer = $unzer;
    }

    //<editor-fold desc="Getters/Setters"

    /** @return Unzer */
    public function getUnzer(): Unzer
    {
        return $this->unzer;
    }

    /**
     * @param Unzer $unzer
     *
     * @return ResourceServiceInterface
     */
    public function setUnzer(Unzer $unzer): ResourceServiceInterface
    {
        $this->unzer = $unzer;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="General">

    /**
     * Send request to API.
     *
     * @param AbstractUnzerResource $resource
     * @param string                $httpMethod
     *
     * @return stdClass
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function send(
        AbstractUnzerResource $resource,
        $httpMethod = HttpAdapterInterface::REQUEST_GET
    ): stdClass {
        $appendId     = $httpMethod !== HttpAdapterInterface::REQUEST_POST;
        $uri          = $resource->getUri($appendId, $httpMethod);
        $responseJson = $resource->getUnzerObject()->getHttpService()->send($uri, $resource, $httpMethod);
        return json_decode($responseJson, false);
    }

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractUnzerResource $resource
     *
     * @return AbstractUnzerResource
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getResource(AbstractUnzerResource $resource): AbstractUnzerResource
    {
        if ($resource->getFetchedAt() === null && $resource->getId() !== null) {
            $this->fetchResource($resource);
        }
        return $resource;
    }

    /**
     * @param $url
     *
     * @return AbstractUnzerResource|null
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fetchResourceByUrl($url)
    {
        $resource = null;
        $unzer    = $this->unzer;

        $resourceId   = IdService::getLastResourceIdFromUrlString($url);
        $resourceType = IdService::getResourceTypeFromIdString($resourceId);
        switch (true) {
            case $resourceType === IdStrings::AUTHORIZE:
                $resource = $unzer->fetchAuthorization(IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT));
                break;
            case $resourceType === IdStrings::CHARGE:
                $resource = $unzer->fetchChargeById(
                    IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT),
                    $resourceId
                );
                break;
            case $resourceType === IdStrings::SHIPMENT:
                $resource = $unzer->fetchShipment(
                    IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT),
                    $resourceId
                );
                break;
            case $resourceType === IdStrings::CANCEL:
                $paymentId  = IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT);
                $chargeId   = IdService::getResourceIdOrNullFromUrl($url, IdStrings::CHARGE);
                if ($chargeId !== null) {
                    $resource = $unzer->fetchRefundById($paymentId, $chargeId, $resourceId);
                    break;
                }
                $resource = $unzer->fetchReversal($paymentId, $resourceId);
                break;
            case $resourceType === IdStrings::PAYOUT:
                $resource = $unzer->fetchPayout(IdService::getResourceIdFromUrl($url, IdStrings::PAYMENT));
                break;
            case $resourceType === IdStrings::PAYMENT:
                $resource = $unzer->fetchPayment($resourceId);
                break;
            case $resourceType === IdStrings::METADATA:
                $resource = $unzer->fetchMetadata($resourceId);
                break;
            case $resourceType === IdStrings::CUSTOMER:
                $resource = $unzer->fetchCustomer($resourceId);
                break;
            case $resourceType === IdStrings::BASKET:
                $resource = $unzer->fetchBasket($resourceId);
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
     * @param AbstractUnzerResource $resource
     *
     * @return AbstractUnzerResource
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function createResource(AbstractUnzerResource $resource): AbstractUnzerResource
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
     * @param AbstractUnzerResource $resource
     *
     * @return AbstractUnzerResource
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function updateResource(AbstractUnzerResource $resource): AbstractUnzerResource
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
     * @param AbstractUnzerResource $resource
     *
     * @return AbstractUnzerResource|null
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function deleteResource(AbstractUnzerResource &$resource): ?AbstractUnzerResource
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
     * Updates the given local resource object (id must be set)
     *
     * @param AbstractUnzerResource $resource The local resource object to update.
     *
     * @return AbstractUnzerResource The updated resource object.
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     * @throws Exception
     */
    public function fetchResource(AbstractUnzerResource $resource): AbstractUnzerResource
    {
        $method = HttpAdapterInterface::REQUEST_GET;
        $response = $this->send($resource, $method);
        $resource->setFetchedAt(new DateTime('now'));
        $resource->handleResponse($response, $method);
        return $resource;
    }

    //</editor-fold>

    //<editor-fold desc="Payout resource">

    /**
     * Fetch an Payout object by its paymentId.
     * Payout Ids are not global but specific to the payment.
     * A Payment object can have zero to one payout.
     *
     * @param Payment|string $payment The Payment object or the id of a Payment object whose Payout to fetch.
     *                                There can only be one payout object to a payment.
     *
     * @return Payout The Payout object of the given Payment.
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fetchPayout($payment): Payout
    {
        $paymentObject = $this->fetchPayment($payment);
        /** @var Payout $payout */
        $payout = $this->fetchResource($paymentObject->getPayout(true));
        return $payout;
    }

    //</editor-fold>

    //<editor-fold desc="Recurring">

    /**
     * {@inheritDoc}
     */
    public function activateRecurringPayment($paymentType, $returnUrl): Recurring
    {
        $paymentTypeObject = $paymentType;
        if (is_string($paymentType)) {
            $paymentTypeObject = $this->fetchPaymentType($paymentType);
        }

        // make sure recurring is allowed for the given payment type.
        if (in_array(CanRecur::class, class_uses($paymentTypeObject), true)) {
            $recurring = new Recurring($paymentTypeObject->getId(), $returnUrl);
            $recurring->setParentResource($this->unzer);
            $this->createResource($recurring);
            return $recurring;
        }

        throw new RuntimeException('Recurring is not available for the given payment type.');
    }

    //</editor-fold>

    //<editor-fold desc="Payment resource">

    /**
     * Fetches the payment object if the id is given.
     * Else it just returns the given payment argument as-is.
     *
     * @param $payment
     *
     * @return AbstractUnzerResource|Payment
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getPaymentResource($payment): Payment
    {
        $paymentObject = $payment;

        if (is_string($payment)) {
            $paymentObject = $this->fetchPayment($payment);
        }
        return $paymentObject;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchPayment($payment): Payment
    {
        $paymentObject = $payment;
        if (is_string($payment)) {
            $paymentObject = new Payment();
            $paymentObject->setId($payment);
        }

        $this->fetchResource($paymentObject->setParentResource($this->unzer));
        return $paymentObject;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchPaymentByOrderId($orderId): Payment
    {
        $paymentObject = (new Payment($this->unzer))->setOrderId($orderId);
        $this->fetchResource($paymentObject);
        return $paymentObject;
    }

    //</editor-fold>

    //<editor-fold desc="Keypair resource">

    /**
     * {@inheritDoc}
     */
    public function fetchKeypair($detailed = false): Keypair
    {
        $keyPair = (new Keypair())->setParentResource($this->unzer)->setDetailed($detailed);
        $this->fetchResource($keyPair);
        return $keyPair;
    }

    //</editor-fold>

    //<editor-fold desc="Metadata resource">

    /**
     * {@inheritDoc}
     */
    public function createMetadata(Metadata $metadata): Metadata
    {
        $metadata->setParentResource($this->unzer);
        $this->createResource($metadata);
        return $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata($metadata): Metadata
    {
        $metadataObject = $metadata;
        if (is_string($metadata)) {
            $metadataObject = (new Metadata())->setId($metadata);
        }

        $this->fetchResource($metadataObject->setParentResource($this->unzer));
        return $metadataObject;
    }

    //</editor-fold>

    //<editor-fold desc="Basket resource">

    /**
     * {@inheritDoc}
     */
    public function createBasket(Basket $basket): Basket
    {
        $basket->setParentResource($this->unzer);
        $this->createResource($basket);
        return $basket;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBasket($basket): Basket
    {
        $basketObj = $basket;
        if (is_string($basket)) {
            $basketObj = (new Basket())->setId($basket);
        }
        $basketObj->setParentResource($this->unzer);

        $this->fetchResource($basketObj);
        return $basketObj;
    }

    /**
     * {@inheritDoc}
     */
    public function updateBasket(Basket $basket): Basket
    {
        $basket->setParentResource($this->unzer);
        $this->updateResource($basket);
        return $basket;
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType resource">

    /**
     * {@inheritDoc}
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        $paymentType->setParentResource($this->unzer);
        $this->createResource($paymentType);
        return $paymentType;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchPaymentType($typeId): BasePaymentType
    {
        $resourceType = IdService::getResourceTypeFromIdString($typeId);
        switch ($resourceType) {
            case IdStrings::ALIPAY:
                $paymentType = new Alipay();
                break;
            case IdStrings::APPLEPAY:
                $paymentType = new Applepay(null, null, null, null);
                break;
            case IdStrings::BANCONTACT:
                $paymentType = new Bancontact();
                break;
            case IdStrings::CARD:
                $paymentType = new Card(null, null);
                break;
            case IdStrings::EPS:
                $paymentType = new EPS();
                break;
            case IdStrings::GIROPAY:
                $paymentType = new Giropay();
                break;
            case IdStrings::HIRE_PURCHASE_DIRECT_DEBIT:
            case IdStrings::INSTALLMENT_SECURED:
                $paymentType = new InstallmentSecured();
                break;
            case IdStrings::IDEAL:
                $paymentType = new Ideal();
                break;
            case IdStrings::INVOICE:
                $paymentType = new Invoice();
                break;
            case IdStrings::INVOICE_FACTORING:
            case IdStrings::INVOICE_GUARANTEED:
            case IdStrings::INVOICE_SECURED:
                $paymentType = new InvoiceSecured();
                break;
            case IdStrings::PAYPAL:
                $paymentType = new Paypal();
                break;
            case IdStrings::PIS:
                $paymentType = new PIS();
                break;
            case IdStrings::PREPAYMENT:
                $paymentType = new Prepayment();
                break;
            case IdStrings::PRZELEWY24:
                $paymentType = new Przelewy24();
                break;
            case IdStrings::SEPA_DIRECT_DEBIT:
                $paymentType = new SepaDirectDebit(null);
                break;
            case IdStrings::SEPA_DIRECT_DEBIT_GUARANTEED:
            case IdStrings::SEPA_DIRECT_DEBIT_SECURED:
                $paymentType = new SepaDirectDebitSecured(null);
                break;
            case IdStrings::SOFORT:
                $paymentType = new Sofort();
                break;
            case IdStrings::WECHATPAY:
                $paymentType = new Wechatpay();
                break;
            default:
                throw new RuntimeException('Invalid payment type!');
                break;
        }

        /** @var BasePaymentType $paymentType */
        $paymentType = $paymentType->setParentResource($this->unzer)->setId($typeId);
        $this->fetchResource($paymentType);
        return $paymentType;
    }

    /**
     * {@inheritDoc}
     */
    public function updatePaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        /** @var BasePaymentType $returnPaymentType */
        $returnPaymentType = $this->updateResource($paymentType);
        return $returnPaymentType;
    }

    //</editor-fold>

    //<editor-fold desc="Customer resource">

    /**
     * {@inheritDoc}
     */
    public function createCustomer(Customer $customer): Customer
    {
        $customer->setParentResource($this->unzer);
        $this->createResource($customer);
        return $customer;
    }

    /**
     * {@inheritDoc}
     */
    public function createOrUpdateCustomer(Customer $customer): Customer
    {
        try {
            $this->createCustomer($customer);
        } catch (UnzerApiException $e) {
            if (ApiResponseCodes::API_ERROR_CUSTOMER_ID_ALREADY_EXISTS !== $e->getCode()) {
                throw $e;
            }

            // fetch Customer resource by customerId
            $fetchedCustomer = $this->fetchCustomerByExtCustomerId($customer->getCustomerId());

            // update the existing customer with the data of the new customer
            $this->updateCustomer($customer->setId($fetchedCustomer->getId()));
        }

        return $customer;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchCustomer($customer): Customer
    {
        $customerObject = $customer;

        if (is_string($customer)) {
            $customerObject = (new Customer())->setId($customer);
        }

        $this->fetchResource($customerObject->setParentResource($this->unzer));
        return $customerObject;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchCustomerByExtCustomerId($customerId): Customer
    {
        $customerObject = (new Customer())->setCustomerId($customerId);
        $this->fetchResource($customerObject->setParentResource($this->unzer));
        return $customerObject;
    }

    /**
     * {@inheritDoc}
     */
    public function updateCustomer(Customer $customer): Customer
    {
        $this->updateResource($customer);
        return $customer;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCustomer($customer): void
    {
        $customerObject = $customer;

        if (is_string($customer)) {
            $customerObject = $this->fetchCustomer($customer);
        }

        $this->deleteResource($customerObject);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization resource">

    /**
     * {@inheritDoc}
     */
    public function fetchAuthorization($payment): Authorization
    {
        $paymentObject = $this->fetchPayment($payment);
        /** @var Authorization $authorize */
        $authorize = $paymentObject->getAuthorization(true);

        if (!$authorize instanceof Authorization) {
            throw new RuntimeException('The payment does not seem to have an Authorization.');
        }

        $this->fetchResource($authorize);
        return $authorize;
    }

    //</editor-fold>

    //<editor-fold desc="Charge resource">
    public function fetchCharge(Charge $charge): Charge
    {
        $this->fetchResource($charge);
        return $charge;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchChargeById($payment, $chargeId): Charge
    {
        $paymentObject = $this->fetchPayment($payment);
        $charge = $paymentObject->getCharge($chargeId, true);

        if (!$charge instanceof Charge) {
            throw new RuntimeException('The charge object could not be found.');
        }

        $this->fetchResource($charge);
        return $charge;
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation resource">

    /**
     * {@inheritDoc}
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): Cancellation
    {
        $this->fetchResource($authorization);
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchReversal($payment, $cancellationId): Cancellation
    {
        /** @var Authorization $authorization */
        $authorization = $this->fetchPayment($payment)->getAuthorization();
        return $authorization->getCancellation($cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchRefundById($payment, $chargeId, $cancellationId): Cancellation
    {
        /** @var Charge $charge */
        $charge = $this->fetchChargeById($payment, $chargeId);
        return $this->fetchRefund($charge, $cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchRefund(Charge $charge, $cancellationId): Cancellation
    {
        /** @var Cancellation $cancel */
        $cancel = $this->fetchResource($charge->getCancellation($cancellationId, true));
        return $cancel;
    }

    //</editor-fold>

    //<editor-fold desc="Shipment resource">

    /**
     * {@inheritDoc}
     */
    public function fetchShipment($payment, $shipmentId): Shipment
    {
        $paymentObject = $this->fetchPayment($payment);
        return $paymentObject->getShipment($shipmentId);
    }

    //</editor-fold>
}
