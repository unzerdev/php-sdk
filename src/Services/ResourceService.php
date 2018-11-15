<?php
/**
 * This service provides for all methods to manage resources with the api.
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

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\IdStrings;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Keypair;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
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
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;

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
     * @return \stdClass
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function send(AbstractHeidelpayResource $resource, $httpMethod = HttpAdapterInterface::REQUEST_GET): \stdClass
    {
        $responseJson = $resource->getHeidelpayObject()->send($resource->getUri(), $resource, $httpMethod);
        return json_decode($responseJson);
    }

    /**
     * @param string $url
     * @param string $typePattern
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getResourceIdFromUrl($url, $typePattern): string
    {
        $matches = [];
        preg_match('~\/([s|p]{1}-' . $typePattern . '-[\d]+)~', $url, $matches);

        if (\count($matches) < 2) {
            throw new \RuntimeException('Id not found!');
        }

        return $matches[1];
    }

    /**
     * Fetches the Resource if necessary.
     *
     * @param AbstractHeidelpayResource $resource
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        if ($resource->getFetchedAt() === null && $resource->getId() !== null) {
            $this->fetch($resource);
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
     * @throws \RuntimeException
     */
    public function create(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        $method = HttpAdapterInterface::REQUEST_POST;
        $response = $this->send($resource, $method);

        $isError = isset($response->isError) && $response->isError;
        if ($isError) {
            return $resource;
        }

        $resource->setId($response->id);

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
     * @throws \RuntimeException
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
     * @return null
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function delete(AbstractHeidelpayResource $resource)
    {
        $this->send($resource, HttpAdapterInterface::REQUEST_DELETE);

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
     * @throws \RuntimeException
     */
    public function fetch(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        $method = HttpAdapterInterface::REQUEST_GET;
        $response = $this->send($resource, $method);
        $resource->setFetchedAt(new \DateTime('now'));
        $resource->handleResponse($response, $method);
        return $resource;
    }

    //</editor-fold>

    //<editor-fold desc="Payment resource">

    /**
     * Fetch and return payment by given payment id.
     *
     * @param Payment|string $payment
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchPayment($payment): AbstractHeidelpayResource
    {
        $paymentObject = $payment;
        if (\is_string($payment)) {
            $paymentObject = new Payment($this->heidelpay);
            $paymentObject->setId($payment);
        }

        $this->fetch($paymentObject);
        if (!$paymentObject instanceof Payment) {
            throw new \RuntimeException('Fetched object is not a payment object!');
        }
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
     * @throws \RuntimeException
     */
    public function fetchKeypair(): AbstractHeidelpayResource
    {
        return $this->fetch(new Keypair($this->heidelpay));
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
     * @throws \RuntimeException
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this->heidelpay);
        return $this->create($paymentType);
    }

    /**
     * Fetch the payment type with the given Id from the API.
     *
     * @param string $typeId
     *
     * @return BasePaymentType|AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchPaymentType($typeId): AbstractHeidelpayResource
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3}|p24)-[a-z0-9]*/', $typeId, $typeIdParts);

        switch ($typeIdParts[1]) {
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
            default:
                throw new \RuntimeException(sprintf('Payment type "%s" is not allowed!', $typeIdParts[1]));
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
     * @throws \RuntimeException
     */
    public function createCustomer(Customer $customer): AbstractHeidelpayResource
    {
        $customer->setParentResource($this->heidelpay);
        return $this->create($customer);
    }

    /**
     * Fetch and return Customer object from API by the given id.
     *
     * @param Customer|string $customer
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function fetchCustomer($customer): AbstractHeidelpayResource
    {
        $customerObject = $customer;

        if (\is_string($customer)) {
            $customerObject = (new Customer())->setId($customer);
        }

        return $this->fetch($customerObject->setParentResource($this->heidelpay));
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
     */
    public function updateCustomer(Customer $customer): AbstractHeidelpayResource
    {
        return $this->update($customer);
    }

    /**
     * Delete the given Customer resource.
     *
     * @param Customer|string $customer
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function deleteCustomer($customer)
    {
        $customerObject = $customer;

        if (\is_string($customer)) {
            $customerObject = $this->fetchCustomer($customer);
        }

        $this->delete($customerObject);
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
     */
    public function fetchChargeById($payment, $chargeId): AbstractHeidelpayResource
    {
        /** @var Payment $paymentObject */
        $paymentObject = $this->fetchPayment($payment);
        return $this->fetch($paymentObject->getChargeById($chargeId, true));
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
     */
    public function fetchShipment($payment, $shipmentId): AbstractHeidelpayResource
    {
        $paymentObject = $this->fetchPayment($payment);
        return $paymentObject->getShipmentById($shipmentId);
    }

    //</editor-fold>
}
