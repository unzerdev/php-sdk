<?php
/**
 * This represents the payment resource.
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
 * @package  heidelpay/mgw_sdk/resources
 */
namespace heidelpay\MgwPhpSdk\Resources;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\IdStrings;
use heidelpay\MgwPhpSdk\Constants\TransactionTypes;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;
use heidelpay\MgwPhpSdk\Traits\HasOrderId;
use heidelpay\MgwPhpSdk\Traits\HasPaymentState;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

class Payment extends AbstractHeidelpayResource
{
    use HasPaymentState;
    use HasOrderId;

    /**
     * @param null $parent
     */
    public function __construct($parent = null)
    {
        $this->amount = new Amount();

        parent::__construct($parent);
    }

    //<editor-fold desc="Properties">
    /** @var string $redirectUrl */
    private $redirectUrl = '';

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
    //</editor-fold>

    //<editor-fold desc="Setters/Getters">

    /**
     * Returns the redirectUrl set by the API.
     *
     * @return string
     */
    public function getRedirectUrl(): string
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
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
        $authorize->setParentResource($this);
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getChargeById($chargeId, $lazy = false)
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getCharge($index, $lazy = false)
    {
        if (isset($this->getCharges()[$index])) {
            $resource = $this->getCharges()[$index];
            if (!$lazy) {
                return $this->getResource($resource);
            }
            return $resource;
        }
        return null;
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
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

        if (\is_string($customer)) {
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
     * Returns the Payment Type object referenced by this Payment or throws a \RuntimeException if none exists.
     *
     * @return BasePaymentType The PaymentType referenced by this Payment.
     *
     * @throws \RuntimeException An exception is thrown when the Payment does not reference a PaymentType.
     */
    public function getPaymentType(): BasePaymentType
    {
        $paymentType = $this->paymentType;
        if (!$paymentType instanceof BasePaymentType) {
            throw new \RuntimeException('The paymentType is not set.');
        }

        return $paymentType;
    }

    /**
     * Sets the Payments reference to the given PaymentType resource.
     * The PaymentType can be either a PaymentType object or the id of a PaymentType resource.
     *
     * @param BasePaymentType|string $paymentType The PaymentType object or the id of the PaymenType to be referenced.
     *
     * @return Payment This Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function setPaymentType($paymentType): Payment
    {
        if (empty($paymentType)) {
            throw new \RuntimeException('Payment type is missing!');
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();

        /** @var BasePaymentType $paymentTypeObject */
        $paymentTypeObject = $paymentType;
        if (\is_string($paymentType)) {
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
     * @return Cancellation The retrieved Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getCancellation($cancellationId, $lazy = false): Cancellation
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

        throw new \RuntimeException('Cancellation #' . $cancellationId . ' does not exist.');
    }

    /**
     * Return an array containing all Cancellations of this Payment object
     * I. e. refunds (charge cancellations) and reversals (authorize cancellations).
     *
     * @return array The array containing all Cancellation objects of this Payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
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
        $shipment->setPayment($this)->setParentResource($this);
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function getShipmentById($shipmentId, $lazy = false)
    {
        /** @var Shipment $shipment */
        foreach ($this->shipments as $shipment) {
            if ($shipment->getId() === $shipmentId) {
                if (!$lazy) {
                    $this->fetchResource($shipment);
                }
                return $shipment;
            }
        }

        return null;
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
    public function getResourcePath()
    {
        return 'payments';
    }

    /**
     * {@inheritDoc}
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function handleResponse(\stdClass $response, $method = HttpAdapterInterface::REQUEST_GET)
    {
        parent::handleResponse($response, $method);

        if (isset($response->state->id)) {
            $this->setState($response->state->id);
        }

        if (isset($response->resources)) {
            $resources = $response->resources;

            if (isset($resources->paymentId)) {
                $this->setId($resources->paymentId);
            }

            if (isset($resources->customerId) && !empty($resources->customerId)) {
                if (!$this->customer instanceof Customer) {
                    $this->customer = $this->getHeidelpayObject()->fetchCustomer($resources->customerId);
                } else {
                    $this->getHeidelpayObject()->fetchCustomer($this->customer);
                }
            }

            if (isset($resources->typeId) && !empty($resources->typeId)) {
                if (!$this->paymentType instanceof BasePaymentType) {
                    $this->paymentType = $this->getHeidelpayObject()->fetchPaymentType($resources->typeId);
                }
            }
        }
        if (isset($response->transactions) && !empty($response->transactions)) {
            foreach ($response->transactions as $transaction) {
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
    }

    //</editor-fold>

    //<editor-fold desc="Transactions">

    /**
     * Performs a Cancellation transaction on the Payment.
     * If no amount is given a full cancel will be performed i. e. all Charges and Authorizations will be cancelled.
     * todo: What happens on cancel with amount?
     *
     * @param float|null $amount The amount to canceled.
     *
     * @return Cancellation The resulting Cancellation object.
     *                      If more then one cancellation is performed the last one will be returned.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancel($amount = null): Cancellation
    {
        $cancel = null;
        $exception = null;

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            try {
                $cancel = $charge->cancel();
            } catch (HeidelpayApiException $e) {
                $exception = $e;
                if (!ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED === $e->getCode()) {
                    throw $e;
                }
            }
        }

        try {
            if ($this->getAuthorization() instanceof Authorization) {
                $cancel = $this->getHeidelpayObject()->cancelAuthorization($this->getAuthorization(), $amount);
            }
        } catch (HeidelpayApiException $e) {
            $exception = $e;
            if (!ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED === $e->getCode()) {
                throw $e;
            }
        }

        if ($cancel instanceof Cancellation) {
            return $cancel;
        }

        // throw the last exception if no cancellation has been created
        if ($exception instanceof HeidelpayApiException) {
            throw $exception;
        }

        throw new \RuntimeException('This Payment could not be cancelled.');
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
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function charge($amount = null, $currency = null): Charge
    {
        if ($this->getAuthorization(true) !== null) {
            return $this->getHeidelpayObject()->chargeAuthorization($this, $amount);
        }
        return $this->getHeidelpayObject()->chargePayment($this, $amount, $currency);
    }

    /**
     * Performs an Authorization on this payment object.
     *
     * @param float                $amount      The amount to be authorized.
     * @param string               $currency    The currency of the amount to be authorized.
     * @param BasePaymentType      $paymentType The PaymentType of this Payment.
     * @param string               $returnUrl   The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null $customer    The Customer object or the id of the Customer to be referenced.
     *                                          No Customer will be referenced if set or left null.
     *
     * @return Authorization The resulting Authorization object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl = null, $customer = null): Authorization
    {
        $this->setPaymentType($paymentType);
        return $this->getHeidelpayObject()->authorizeWithPayment($amount, $currency, $this, $returnUrl, $customer);
    }

    /**
     * Performs a Shipment transaction on this Payment.
     *
     * @return AbstractHeidelpayResource|Shipment The resulting Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    public function ship()
    {
        return $this->getHeidelpayObject()->ship($this);
    }

    //</editor-fold>

    //<editor-fold desc="Transaction Update">

    /**
     * This updates the local Authorization object referenced by this Payment with the given Authorization transaction
     * from the Payment response.
     *
     * @param \stdClass $transaction The transaction from the Payment response containing the Authorization data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateAuthorizationTransaction($transaction)
    {
        $transactionId = $this->getResourceIdFromUrl($transaction->url, IdStrings::AUTHORIZE);
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            $authorization = (new Authorization())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($transactionId);
            $this->setAuthorization($authorization);
        }
        $authorization->setAmount($transaction->amount);
    }

    /**
     * This updates the local Charge object referenced by this Payment with the given Charge transaction from the
     * Payment response.
     *
     * @param \stdClass $transaction The transaction from the Payment response containing the Charge data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateChargeTransaction($transaction)
    {
        $transactionId = $this->getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);
        $charge = $this->getChargeById($transactionId, true);
        if (!$charge instanceof Charge) {
            $charge = (new Charge())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($transactionId);
            $this->addCharge($charge);
        }
        $charge->setAmount($transaction->amount);
    }

    /**
     * This updates a local Authorization Cancellation object (aka. reversal) referenced by this Payment with the
     * given Cancellation transaction from the Payment response.
     *
     * @param \stdClass $transaction The transaction from the Payment response containing the Cancellation data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateReversalTransaction($transaction)
    {
        $transactionId = $this->getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            throw new \RuntimeException('The Authorization object can not be found.');
        }

        $cancellation = $authorization->getCancellation($transactionId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation =  (new Cancellation())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($transactionId);
            $authorization->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    /**
     * This updates a local Charge Cancellation object (aka. refund) referenced by this Payment with the given
     * Cancellation transaction from the Payment response.
     *
     * @param \stdClass $transaction The transaction from the Payment response containing the Cancellation data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateRefundTransaction($transaction)
    {
        $refundId = $this->getResourceIdFromUrl($transaction->url, IdStrings::CANCEL);
        $chargeId = $this->getResourceIdFromUrl($transaction->url, IdStrings::CHARGE);

        $charge = $this->getChargeById($chargeId, true);
        if (!$charge instanceof Charge) {
            throw new \RuntimeException('Charge object does not exist.');
        }

        $cancellation = $charge->getCancellation($refundId, true);
        if (!$cancellation instanceof Cancellation) {
            $cancellation =  (new Cancellation())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($refundId);
            $charge->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    /**
     * This updates the local Shipment object referenced by this Payment with the given Shipment transaction from the
     * Payment response.
     *
     * @param \stdClass $transaction The transaction from the Payment response containing the Shipment data.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws \RuntimeException     A \RuntimeException is thrown when there is a error while using the SDK.
     */
    private function updateShipmentTransaction($transaction)
    {
        $shipmentId = $this->getResourceIdFromUrl($transaction->url, IdStrings::SHIPMENT);
        $shipment = $this->getShipmentById($shipmentId, true);
        if (!$shipment instanceof Shipment) {
            $shipment = new Shipment(null, $shipmentId);
            $this->addShipment($shipment);
        }
        $shipment->setAmount($transaction->amount);
    }

    //</editor-fold>
}
