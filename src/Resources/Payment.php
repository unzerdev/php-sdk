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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Resources
 */
namespace heidelpayPHP\Resources;

use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Constants\IdStrings;
use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\EmbeddedResources\Amount;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\IdService;
use heidelpayPHP\Traits\HasInvoiceId;
use heidelpayPHP\Traits\HasOrderId;
use heidelpayPHP\Traits\HasPaymentState;
use heidelpayPHP\Traits\HasTraceId;
use RuntimeException;
use stdClass;

use function count;
use function in_array;
use function is_string;

class Payment extends AbstractHeidelpayResource
{
    use HasPaymentState;
    use HasOrderId;
    use HasInvoiceId;
    use HasTraceId;

    /** @var string $redirectUrl */
    private $redirectUrl;

    /** @var Authorization $authorization */
    private $authorization;

    /** @var Payout $payout */
    private $payout;

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

    /** @var Metadata|null $metadata */
    private $metadata;

    /** @var Basket $basket */
    private $basket;

    /**
     * @param null $parent
     */
    public function __construct($parent = null)
    {
        $this->amount = new Amount();

        $this->setParentResource($parent);
    }

    //<editor-fold desc="Setters/Getters">

    /**
     * Returns the redirectUrl set by the API.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the redirectUrl via response from API.
     *
     * @param string|null $redirectUrl
     *
     * @return Payment
     */
    protected function setRedirectUrl($redirectUrl): Payment
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
     * Retrieves the Payout object of this payment.
     * Fetches the Payout if it has not been fetched before and the lazy flag is not set.
     * Returns null if the Payout does not exist.
     *
     * @param bool $lazy Enables lazy loading if set to true which results in the object not being updated via
     *                   API and possibly containing just the meta data known from the Payment object response.
     *
     * @return Payout|AbstractHeidelpayResource|null The Payout object if it exists.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getPayout($lazy = false)
    {
        $payout = $this->payout;
        if (!$lazy && $payout !== null) {
            return $this->getResource($payout);
        }
        return $payout;
    }

    /**
     * Sets the Payout object.
     *
     * @param Payout $payout The Payout object to be stored in the payment.
     *
     * @return Payment This Payment object.
     */
    public function setPayout(Payout $payout): Payment
    {
        $payout->setPayment($this);
        $this->payout = $payout;
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getCharge($chargeId, $lazy = false): ?Charge
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
     * @param Customer|string|null $customer The Customer object or the id of the Customer to be referenced by the Payment.
     *
     * @return Payment This Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function setCustomer($customer): Payment
    {
        if (empty($customer)) {
            return $this;
        }

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
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * Returns the Payment Type object referenced by this Payment or throws a RuntimeException if none exists.
     *
     * @return BasePaymentType|null The PaymentType referenced by this Payment.
     */
    public function getPaymentType(): ?BasePaymentType
    {
        return $this->paymentType;
    }

    /**
     * Sets the Payments reference to the given PaymentType resource.
     * The PaymentType can be either a PaymentType object or the id of a PaymentType resource.
     *
     * @param mixed $paymentType The PaymentType object or the id of the PaymentType to be referenced.
     *
     * @return Payment This Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function setPaymentType($paymentType): Payment
    {
        if (empty($paymentType)) {
            return $this;
        }

        $heidelpay = $this->getHeidelpayObject();

        /** @var BasePaymentType $paymentTypeObject */
        $paymentTypeObject = $paymentType;
        if (is_string($paymentType)) {
            $paymentTypeObject = $heidelpay->fetchPaymentType($paymentType);
        } elseif ($paymentTypeObject instanceof BasePaymentType && !$paymentTypeObject instanceof Paypage) {
            if ($paymentTypeObject->getId() === null) {
                $heidelpay->createPaymentType($paymentType);
            }
        }

        $this->paymentType = $paymentTypeObject;
        return $this;
    }

    /**
     * @return Metadata|null
     */
    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }

    /**
     * @param Metadata|null $metadata
     *
     * @return Payment
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function setMetadata($metadata): Payment
    {
        if (!$metadata instanceof Metadata) {
            return $this;
        }
        $this->metadata = $metadata;

        $heidelpay = $this->getHeidelpayObject();
        if ($this->metadata->getId() === null) {
            $heidelpay->getResourceService()->createResource($this->metadata->setParentResource($heidelpay));
        }

        return $this;
    }

    /**
     * @return Basket|null
     */
    public function getBasket(): ?Basket
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function setBasket($basket): Payment
    {
        $this->basket = $basket;

        if (!$basket instanceof Basket) {
            return $this;
        }

        $heidelpay = $this->getHeidelpayObject();
        if ($this->basket->getId() === null) {
            $heidelpay->getResourceService()->createResource($this->basket->setParentResource($heidelpay));
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getCancellation($cancellationId, $lazy = false): ?Cancellation
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getCancellations(): array
    {
        $refunds = [];

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            $refunds[] = $charge->getCancellations();
        }

        $authorization = $this->getAuthorization(true);
        return array_merge($authorization ? $authorization->getCancellations() : [], ...$refunds);
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function getShipment($shipmentId, $lazy = false): ?Shipment
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
     * Returns the initial transaction (Authorize or Charge) of the payment.
     *
     * @param bool $lazy
     *
     * @return AbstractTransactionType|null
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function getInitialTransaction($lazy = false): ?AbstractTransactionType
    {
        return $this->getAuthorization($lazy) ?? $this->getChargeByIndex(0, $lazy);
    }

    /**
     * Sets the currency string of the amounts of this Payment.
     *
     * @param string $currency
     *
     * @return self
     */
    protected function setCurrency(string $currency): self
    {
        $this->amount->handleResponse((object)['currency' => $currency]);
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET): void
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
    public function getExternalId(): ?string
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
     * @param string     $reason
     *
     * @return Cancellation|null The resulting Cancellation object.
     *                           If more then one cancellation is performed the last one will be returned.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @deprecated since 1.2.3.0
     * @see Payment::cancelAmount()
     */
    public function cancel($amount = null, $reason = CancelReasonCodes::REASON_CODE_CANCEL): ?Cancellation
    {
        $cancellations = $this->cancelAmount($amount, $reason);

        if (count($cancellations) > 0) {
            return $cancellations[0];
        }

        throw new RuntimeException('This Payment could not be cancelled.');
    }

    /**
     * Performs a Cancellation transaction on the Payment.
     * If no amount is given a full cancel will be performed i. e. all Charges and Authorizations will be cancelled.
     *
     * @param float|null  $amount           The amount to be canceled.
     *                                      This will be sent as amountGross in case of Hire Purchase payment method.
     * @param string|null $reasonCode       Reason for the Cancellation ref \heidelpayPHP\Constants\CancelReasonCodes.
     * @param string|null $paymentReference A reference string for the payment.
     * @param float|null  $amountNet        The net value of the amount to be cancelled (Hire Purchase only).
     * @param float|null  $amountVat        The vat value of the amount to be cancelled (Hire Purchase only).
     *
     * @return Cancellation[] An array holding all Cancellation objects created with this cancel call.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelAmount(
        float $amount = null,
        $reasonCode = CancelReasonCodes::REASON_CODE_CANCEL,
        string $paymentReference = null,
        float $amountNet = null,
        float $amountVat = null
    ): array {
        return $this->getHeidelpayObject()->cancelPayment($this, $amount, $reasonCode, $paymentReference, $amountNet, $amountVat);
    }

    /**
     * Cancels all charges of the payment and returns an array of the cancellations and exceptions that occur.
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     *
     * @deprecated since 1.2.3.0
     * @see Payment::cancelAmount()
     */
    public function cancelAllCharges(): array
    {
        $cancels    = [];
        $exceptions = [];

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            try {
                $cancels[] = $charge->cancel();
            } catch (HeidelpayApiException $e) {
                $allowedErrors = [
                    ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK,
                    ApiResponseCodes::API_ERROR_ALREADY_CANCELLED
                ];
                if (!in_array($e->getCode(), $allowedErrors, true)) {
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
     *
     * @deprecated since 1.2.3.0
     * @see Payment::cancelAuthorizationAmount()
     */
    public function cancelAuthorization($amount = null): array
    {
        $cancels = [];
        $cancel  = $this->cancelAuthorizationAmount($amount);

        if ($cancel instanceof Cancellation) {
            $cancels[] = $cancel;
        }

        return array($cancels, []);
    }

    /**
     * Cancel the given amount of the payments authorization.
     *
     * @param float|null $amount The amount to be cancelled. If null the remaining uncharged amount of the authorization
     *                           will be cancelled completely. If it exceeds the remaining uncharged amount the
     *                           cancellation will only cancel the remaining uncharged amount.
     *
     * @return Cancellation|null The resulting cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelAuthorizationAmount(float $amount = null): ?Cancellation
    {
        return $this->getHeidelpayObject()->cancelPaymentAuthorization($this, $amount);
    }

    /**
     * Performs a Charge transaction on the payment.
     *
     * @param null $amount   The amount to be charged.
     * @param null $currency The currency of the charged amount.
     *
     * @return Charge|AbstractHeidelpayResource The resulting Charge object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function charge($amount = null, $currency = null): Charge
    {
        return $this->getHeidelpayObject()->chargePayment($this, $amount, $currency);
    }

    /**
     * Performs a Shipment transaction on this Payment.
     *
     * @param string|null $invoiceId The id of the invoice in the shop.
     * @param string|null $orderId   The id of the order in the shop.
     *
     * @return AbstractHeidelpayResource|Shipment The resulting Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function ship($invoiceId = null, $orderId = null)
    {
        return $this->getHeidelpayObject()->ship($this, $invoiceId, $orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Payment Update">

    /**
     * @param array $transactions
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateResponseTransactions(array $transactions = []): void
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
                case TransactionTypes::PAYOUT:
                    $this->updatePayoutTransaction($transaction);
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateResponseResources($resources): void
    {
        if (isset($resources->paymentId)) {
            $this->setId($resources->paymentId);
        }

        $customerId = $resources->customerId ?? null;
        if (!empty($customerId)) {
            if ($this->customer instanceof Customer && $this->customer->getId() === $customerId) {
                $this->getResource($this->customer);
            } else {
                $this->customer = $this->getHeidelpayObject()->fetchCustomer($customerId);
            }
        }

        if (isset($resources->typeId) && !empty($resources->typeId) && !$this->paymentType instanceof BasePaymentType) {
            $this->paymentType = $this->getHeidelpayObject()->fetchPaymentType($resources->typeId);
        }

        $metadataId = $resources->metadataId ?? null;
        if (!empty($metadataId)) {
            if ($this->metadata instanceof Metadata && $this->metadata->getId() === $metadataId) {
                $this->getResource($this->metadata);
            } else {
                $this->metadata = $this->getHeidelpayObject()->fetchMetadata($resources->metadataId);
            }
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateAuthorizationTransaction($transaction): void
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateChargeTransaction($transaction): void
    {
        $transactionId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);
        $charge        = $this->getCharge($transactionId, true);
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateReversalTransaction($transaction): void
    {
        $transactionId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            throw new RuntimeException('The Authorization object can not be found.');
        }

        $cancellation = $authorization->getCancellation($transactionId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation = (new Cancellation())->setPayment($this)->setId($transactionId);
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateRefundTransaction($transaction): void
    {
        $refundId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $chargeId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);

        $charge = $this->getCharge($chargeId, true);
        if (!$charge instanceof Charge) {
            throw new RuntimeException('The Charge object can not be found.');
        }

        $cancellation = $charge->getCancellation($refundId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation = (new Cancellation())->setPayment($this)->setId($refundId);
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
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updateShipmentTransaction($transaction): void
    {
        $shipmentId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::SHIPMENT);
        $shipment   = $this->getShipment($shipmentId, true);
        if (!$shipment instanceof Shipment) {
            $shipment = (new Shipment())->setId($shipmentId);
            $this->addShipment($shipment);
        }
        $shipment->setAmount($transaction->amount);
    }

    /**
     * This updates the local Payout object referenced by this Payment with the given Payout transaction from the
     * Payment response.
     *
     * @param stdClass $transaction The transaction from the Payment response containing the Payout data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    private function updatePayoutTransaction($transaction): void
    {
        $payoutId = IdService::getResourceIdFromUrl($transaction->url, IdStrings::PAYOUT);
        $payout   = $this->getPayout(true);
        if (!$payout instanceof Payout) {
            $payout = (new Payout())->setId($payoutId);
            $this->setPayout($payout);
        }
        $payout->setAmount($transaction->amount);
    }

    //</editor-fold>
}
