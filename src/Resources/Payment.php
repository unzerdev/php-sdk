<?php
/**
 * This represents the payment resource.
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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\IdStrings;
use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\EmbeddedResources\Amount;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\IdService;
use heidelpayPHP\Traits\HasOrderId;
use heidelpayPHP\Traits\HasPaymentState;
use function is_string;
use RuntimeException;
use stdClass;

class Payment extends AbstractHeidelpayResource
{
    use HasPaymentState;
    use HasOrderId;

    /** @var string $redirectUrl */
    private $redirectUrl;

    /** @var Authorization $authorization */
    private $authorization;

    /** @var array $shipments */
    private $shipments = [];

    /** @var array $charges */
    private $charges = [];

    /** @var Customer $customer */
    private $customer;

    /** @var BasePaymentType $paymentType */
    private $paymentType;

    /** @var Amount $amount */
    protected $amount;

    /** @var Metadata $metadata */
    private $metadata;

    /** @var Basket $basket */
    private $basket;

    /**
     * @param null $parent
     */
    public function __construct($parent = null)
    {
        $this->amount = new Amount();
        $this->metadata = new Metadata();

        $this->setParentResource($parent);
    }

    //<editor-fold desc="Setters/Getters">

    /**
     * Returns the redirectUrl set by the API.
     *
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the redirectUrl via response from API.
     *
     * @param string $redirectUrl
     *
     * @return Payment
     */
    public function setRedirectUrl(string $redirectUrl): Payment
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    /**
     * Retrieves the Authorization object of this payment.
     * Fetches the Authorization if it has not been fetched before and the lazy flag is not set.
     * Returns null if the Authorization does not exist.
     *
     * @param bool $lazy Enables lazy loading if set to true which results in the object not being updated via
     *                   API and possibly containing just the meta data known from the Payment object response.
     *
     * @return Authorization|AbstractHeidelpayResource|null The Authorization object if it exists.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getAuthorization($lazy = false)
    {
        $authorization = $this->authorization;
        if (!$lazy && $authorization !== null) {
            return $this->getResource($authorization);
        }
        return $authorization;
    }

    /**
     * Sets the Authorization object.
     *
     * @param Authorization $authorize The Authorization object to be stored in the payment.
     *
     * @return Payment This Payment object.
     */
    public function setAuthorization(Authorization $authorize): Payment
    {
        $authorize->setPayment($this);
        $this->authorization = $authorize;
        return $this;
    }

    /**
     * Returns an array containing all known Charges of this Payment.
     *
     * @return array
     */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /**
     * Adds a Charge object to this Payment and stores it in the charges array.
     *
     * @param Charge $charge
     *
     * @return $this
     */
    public function addCharge(Charge $charge): self
    {
        $charge->setPayment($this);
        $this->charges[] = $charge;
        return $this;
    }

    /**
     * Retrieves a Charge object from the charges array of this Payment object by its Id.
     * Fetches the Charge if it has not been fetched before and the lazy flag is not set.
     * Returns null if the Charge does not exist.
     *
     * @param string $chargeId The id of the Charge to be retrieved.
     * @param bool   $lazy     Enables lazy loading if set to true which results in the object not being updated via
     *                         API and possibly containing just the meta data known from the Payment object response.
     *
     * @return Charge|null The retrieved Charge object or null if it does not exist.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getCharge($chargeId, $lazy = false)
    {
        /** @var Charge $charge */
        foreach ($this->charges as $charge) {
            if ($charge->getId() === $chargeId) {
                if (!$lazy) {
                    $this->getResource($charge);
                }
                return $charge;
            }
        }
        return null;
    }

    /**
     * Retrieves a Charge object by its index in the charges array.
     * Fetches the Charge if it has not been fetched before and the lazy flag is not set.
     * Returns null if the Charge does not exist.
     *
     * @param int  $index The index of the desired Charge object within the charges array.
     * @param bool $lazy  Enables lazy loading if set to true which results in the object not being updated via
     *                    API and possibly containing just the meta data known from the Payment object response.
     *
     * @return AbstractHeidelpayResource|Charge|null The retrieved Charge object or null if it could not be found.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getChargeByIndex($index, $lazy = false)
    {
        $resource = null;
        if (isset($this->getCharges()[$index])) {
            $resource = $this->getCharges()[$index];
            if (!$lazy) {
                $resource = $this->getResource($resource);
            }
        }
        return $resource;
    }

    /**
     * Reference this payment object to the passed Customer resource.
     * The Customer resource can be passed as Customer object or the Id of a Customer resource.
     * If the Customer object has not been created yet via API this is done automatically.
     *
     * @param Customer|string $customer The Customer object or the id of the Customer to be referenced by the Payment.
     *
     * @return Payment This Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function setCustomer($customer): Payment
    {
        if (empty($customer)) {
            return $this;
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();

        /** @var Customer $customerObject */
        $customerObject = $customer;

        if (is_string($customer)) {
            $customerObject = $heidelpay->fetchCustomer($customer);
        } elseif ($customerObject instanceof Customer) {
            if ($customerObject->getId() === null) {
                $heidelpay->createCustomer($customerObject);
            }
        }

        $customerObject->setParentResource($heidelpay);
        $this->customer = $customerObject;
        return $this;
    }

    /**
     * Returns the Customer object referenced by this Payment.
     *
     * @return Customer|null The Customer object referenced by this Payment or null if no Customer could be found.
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Returns the Payment Type object referenced by this Payment or throws a RuntimeException if none exists.
     *
     * @return BasePaymentType|null The PaymentType referenced by this Payment.
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Sets the Payments reference to the given PaymentType resource.
     * The PaymentType can be either a PaymentType object or the id of a PaymentType resource.
     *
     * @param BasePaymentType|string $paymentType The PaymentType object or the id of the PaymentType to be referenced.
     *
     * @return Payment This Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function setPaymentType($paymentType): Payment
    {
        if (empty($paymentType)) {
            return $this;
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();

        /** @var BasePaymentType $paymentTypeObject */
        $paymentTypeObject = $paymentType;
        if (is_string($paymentType)) {
            $paymentTypeObject = $heidelpay->fetchPaymentType($paymentType);
        } elseif ($paymentTypeObject instanceof BasePaymentType) {
            if ($paymentTypeObject->getId() === null) {
                $heidelpay->createPaymentType($paymentType);
            }
        }

        $this->paymentType = $paymentTypeObject;
        return $this;
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @param Metadata|null $metadata
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function setMetadata($metadata): Payment
    {
        if ($metadata instanceof Metadata) {
            $this->metadata = $metadata;
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();
        if ($this->metadata->getId() === null) {
            $heidelpay->getResourceService()->create($this->metadata->setParentResource($heidelpay));
        }

        return $this;
    }

    /**
     * @return Basket|null
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * Sets the basket object and creates it automatically if it does not exist yet (i. e. does not have an id).
     *
     * @param Basket|null $basket
     *
     * @return Payment
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function setBasket($basket): Payment
    {
        $this->basket = $basket;

        if (!$basket instanceof Basket) {
            return $this;
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();
        if ($this->basket->getId() === null) {
            $heidelpay->getResourceService()->create($this->basket->setParentResource($heidelpay));
        }

        return $this;
    }

    /**
     * Retrieves a Cancellation object of this payment by its Id.
     * I. e. refunds (charge cancellations) and reversals (authorize cancellations).
     * Fetches the Authorization if it has not been fetched before and the lazy flag is not set.
     * Returns null if the Authorization does not exist.
     *
     * @param string $cancellationId The id of the Cancellation object to be retrieved.
     * @param bool   $lazy           Enables lazy loading if set to true which results in the object not being updated
     *                               via API and possibly containing just the meta data known from the Payment object
     *                               response.
     *
     * @return Cancellation|null The retrieved Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getCancellation($cancellationId, $lazy = false)
    {
        /** @var Cancellation $cancellation */
        foreach ($this->getCancellations() as $cancellation) {
            if ($cancellation->getId() === $cancellationId) {
                if (!$lazy) {
                    $this->getResource($cancellation);
                }
                return $cancellation;
            }
        }

        return null;
    }

    /**
     * Return an array containing all Cancellations of this Payment object
     * I. e. refunds (charge cancellations) and reversals (authorize cancellations).
     *
     * @return array The array containing all Cancellation objects of this Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getCancellations(): array
    {
        $refunds = [];

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            $refunds[] = $charge->getCancellations();
        }

        $authorization = $this->getAuthorization(true);
        $cancellations = array_merge($authorization ? $authorization->getCancellations() : [], ...$refunds);
        return $cancellations;
    }

    /**
     * Add a Shipment object to the shipments array of this Payment object.
     *
     * @param Shipment $shipment The Shipment object to be added to this Payment.
     *
     * @return Payment This payment Object.
     */
    public function addShipment(Shipment $shipment): Payment
    {
        $shipment->setPayment($this);
        $this->shipments[] = $shipment;
        return $this;
    }

    /**
     * Returns all Shipment transactions of this payment.
     *
     * @return array
     */
    public function getShipments(): array
    {
        return $this->shipments;
    }

    /**
     * Retrieves a Shipment object of this Payment by its id.
     *
     * @param string $shipmentId The id of the Shipment to be retrieved.
     * @param bool   $lazy       Enables lazy loading if set to true which results in the object not being updated via
     *                           API and possibly containing just the meta data known from the Payment object response.
     *
     * @return Shipment|null The retrieved Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getShipment($shipmentId, $lazy = false)
    {
        /** @var Shipment $shipment */
        foreach ($this->getShipments() as $shipment) {
            if ($shipment->getId() === $shipmentId) {
                if (!$lazy) {
                    $this->getResource($shipment);
                }
                return $shipment;
            }
        }

        return null;
    }

    /**
     * Sets the Amount object of this Payment.
     * The Amount stores the total, remaining, charged and cancelled amount of this Payment.
     *
     * @param Amount $amount
     *
     * @return Payment
     */
    public function setAmount(Amount $amount): Payment
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Returns the Amount object of this Payment.
     * The Amount stores the total, remaining, charged and cancelled amount of this Payment.
     *
     * @return Amount The Amount object belonging to this Payment.
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * Returns the currency of the amounts of this Payment.
     *
     * @return string The Currency string of this Payment.
     */
    public function getCurrency(): string
    {
        return $this->amount->getCurrency();
    }

    /**
     * Sets the currency string of the amounts of this Payment.
     *
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency(string $currency): self
    {
        $this->amount->setCurrency($currency);
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'payments';
    }

    /**
     * {@inheritDoc}
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        parent::handleResponse($response, $method);

        if (isset($response->state->id)) {
            $this->setState($response->state->id);
        }

        if (isset($response->resources)) {
            $this->updateResponseResources($response->resources);
        }

        if (isset($response->transactions)) {
            $this->updateResponseTransactions($response->transactions);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getExternalId()
    {
        return $this->getOrderId();
    }

    //</editor-fold>

    //<editor-fold desc="Transactions">

    /**
     * Performs a Cancellation transaction on the Payment.
     * If no amount is given a full cancel will be performed i. e. all Charges and Authorizations will be cancelled.
     *
     * @param float|null $amount The amount to canceled.
     *
     * @return Cancellation The resulting Cancellation object.
     *                      If more then one cancellation is performed the last one will be returned.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancel($amount = null): Cancellation
    {
        list($chargeCancels, $chargeExceptions) = $this->cancelAllCharges();
        list($authCancel, $authException) = $this->cancelAuthorization($amount);

        $cancels = array_merge($chargeCancels, $authCancel);
        $exceptions = array_merge($chargeExceptions, $authException);

        if (isset($cancels[0]) && $cancels[0] instanceof Cancellation) {
            return $cancels[0];
        }

        // throw the last exception if no cancellation has been created
        if (isset($exceptions[0]) && $exceptions[0] instanceof HeidelpayApiException) {
            throw $exceptions[0];
        }

        throw new RuntimeException('This Payment could not be cancelled.');
    }

    /**
     * Cancels all charges of the payment and returns an array of the cancellations and already charged exceptions that
     * occur.
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function cancelAllCharges(): array
    {
        $cancels = [];
        $exceptions = [];

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            try {
                $cancels[] = $charge->cancel();
            } catch (HeidelpayApiException $e) {
                if (!in_array($e->getCode(),
                              [
                                  ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK,
                                  ApiResponseCodes::API_ERROR_AUTHORIZE_ALREADY_CANCELLED
                              ],
                      true
                )) {
                    throw $e;
                }
                $exceptions[] = $e;
            }
        }
        return array($cancels, $exceptions);
    }

    /**
     * @param float|null $amount
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function cancelAuthorization($amount = null): array
    {
        $cancels = [];
        $exceptions = [];

        $authorization = $this->getAuthorization();
        if ($authorization instanceof Authorization) {
            try {
                $cancels[] = $authorization->cancel($amount);
            } catch (HeidelpayApiException $e) {
                if (ApiResponseCodes::API_ERROR_AUTHORIZE_ALREADY_CANCELLED !== $e->getCode()) {
                    throw $e;
                }
                $exceptions[] = $e;
            }
        }
        return array($cancels, $exceptions);
    }

    /**
     * Performs a Charge transaction on the payment.
     *
     * @param null $amount   The amount to be charged.
     * @param null $currency The currency of the charged amount.
     *
     * @return Charge The resulting Charge object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function charge($amount = null, $currency = null): Charge
    {
        return $this->getHeidelpayObject()->chargePayment($this, $amount, $currency);
    }

    /**
     * Performs a Shipment transaction on this Payment.
     *
     * @param string|null $invoiceId The id of the invoice in the shop.
     *
     * @return AbstractHeidelpayResource|Shipment The resulting Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function ship($invoiceId = null)
    {
        return $this->getHeidelpayObject()->ship($this, $invoiceId);
    }

    //</editor-fold>

    //<editor-fold desc="Payment Update">

    /**
     * @param array $transactions
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    private function updateResponseTransactions(array $transactions = [])
    {
        if (empty($transactions)) {
            return;
        }

        foreach ($transactions as $transaction) {
            switch ($transaction->type) {
                case TransactionTypes::AUTHORIZATION:
                    $this->updateAuthorizationTransaction($transaction);
                    break;
                case TransactionTypes::CHARGE:
                    $this->updateChargeTransaction($transaction);
                    break;
                case TransactionTypes::REVERSAL:
                    $this->updateReversalTransaction($transaction);
                    break;
                case TransactionTypes::REFUND:
                    $this->updateRefundTransaction($transaction);
                    break;
                case TransactionTypes::SHIPMENT:
                    $this->updateShipmentTransaction($transaction);
                    break;
                default:
                    // skip
                    break;
            }
        }
    }

    /**
     * Handles the resources from a response and updates the payment object accordingly.
     *
     * @param $resources
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    private function updateResponseResources($resources)
    {
        if (isset($resources->paymentId)) {
            $this->setId($resources->paymentId);
        }

        if (isset($resources->customerId) && !empty($resources->customerId)) {
            if ($this->customer instanceof Customer) {
                $this->getResource($this->customer);
            } else {
                $this->customer = $this->getHeidelpayObject()->fetchCustomer($resources->customerId);
            }
        }

        if (isset($resources->typeId) && !empty($resources->typeId) && !$this->paymentType instanceof BasePaymentType) {
            $this->paymentType = $this->getHeidelpayObject()->fetchPaymentType($resources->typeId);
        }

        if (isset($resources->metadataId) && !empty($resources->metadataId) && $this->metadata->getId() === null) {
            $this->metadata = $this->getHeidelpayObject()->fetchMetadata($resources->metadataId);
        }

        if (isset($resources->basketId) && !empty($resources->basketId) && !$this->basket instanceof Basket) {
            $this->basket = $this->getHeidelpayObject()->fetchBasket($resources->basketId);
        }
    }

    /**
     * This updates the local Authorization object referenced by this Payment with the given Authorization transaction
     * from the Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Authorization data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateAuthorizationTransaction($transaction)
    {
        $transactionId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::AUTHORIZE);
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            $authorization = (new Authorization())->setPayment($this)->setId($transactionId);
            $this->setAuthorization($authorization);
        }
        $authorization->setAmount($transaction->amount);
    }

    /**
     * This updates the local Charge object referenced by this Payment with the given Charge transaction from the
     * Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Charge data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateChargeTransaction($transaction)
    {
        $transactionId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);
        $charge = $this->getCharge($transactionId, true);
        if (!$charge instanceof Charge) {
            $charge = (new Charge())->setPayment($this)->setId($transactionId);
            $this->addCharge($charge);
        }
        $charge->setAmount($transaction->amount);
    }

    /**
     * This updates a local Authorization Cancellation object (aka. reversal) referenced by this Payment with the
     * given Cancellation transaction from the Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Cancellation data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateReversalTransaction($transaction)
    {
        $transactionId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            throw new RuntimeException('The Authorization object can not be found.');
        }

        $cancellation = $authorization->getCancellation($transactionId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation =  (new Cancellation())->setPayment($this)->setId($transactionId);
            $authorization->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    /**
     * This updates a local Charge Cancellation object (aka. refund) referenced by this Payment with the given
     * Cancellation transaction from the Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Cancellation data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateRefundTransaction($transaction)
    {
        $refundId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $chargeId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);

        $charge = $this->getCharge($chargeId, true);
        if (!$charge instanceof Charge) {
            throw new RuntimeException('The Charge object can not be found.');
        }

        $cancellation = $charge->getCancellation($refundId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation =  (new Cancellation())->setPayment($this)->setId($refundId);
            $charge->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    /**
     * This updates the local Shipment object referenced by this Payment with the given Shipment transaction from the
     * Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Shipment data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateShipmentTransaction($transaction)
    {
        $shipmentId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::SHIPMENT);
        $shipment = $this->getShipment($shipmentId, true);
        if (!$shipment instanceof Shipment) {
            $shipment = (new Shipment())->setId($shipmentId);
            $this->addShipment($shipment);
        }
        $shipment->setAmount($transaction->amount);
    }

    //</editor-fold>
}
