<?php
/**
 * This represents the payment resource.
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
 * @package  heidelpay/mgw_sdk/resources
 */
namespace heidelpay\MgwPhpSdk\Resources;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\TransactionTypes;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
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
     * Payment constructor.
     *
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
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
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
     * Returns the Authorization object of the payment.
     * Fetches the object first if it has not entirely been fetched before and the lazy flag is set to false.
     *
     * @param bool $lazy
     *
     * @return Authorization|AbstractHeidelpayResource|null
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function getAuthorization($lazy = false)
    {
        $authorization = $this->authorization;
        if (!$lazy && $authorization !== null) {
            return $this->getHeidelpayObject()->getResourceService()->getResource($authorization);
        }
        return $authorization;
    }

    /**
     * @param Authorization $authorize
     *
     * @return Payment
     */
    public function setAuthorization(Authorization $authorize): Payment
    {
        $authorize->setPayment($this);
        $authorize->setParentResource($this);
        $this->authorization = $authorize;
        return $this;
    }

    /**
     * @return array
     */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /**
     * @param array $charges
     *
     * @return Payment
     */
    public function setCharges(array $charges): self
    {
        $this->charges = $charges;
        return $this;
    }

    /**
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
     * Get and return a Charge object by id.
     * Return null if the Charge object does not exist.
     *
     * @param string  $chargeId
     * @param boolean $lazy
     *
     * @return Charge|null
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function getChargeById($chargeId, $lazy = false)
    {
        /** @var Charge $charge */
        foreach ($this->charges as $charge) {
            if ($charge->getId() === $chargeId) {
                if (!$lazy) {
                    $this->getHeidelpayObject()->getResourceService()->getResource($charge);
                }
                return $charge;
            }
        }
        return null;
    }

    /**
     * Get and return a Charge object by array index.
     *
     * @param int  $index
     * @param bool $lazy
     *
     * @return AbstractHeidelpayResource|Charge|null
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function getCharge($index, $lazy = false)
    {
        if (isset($this->getCharges()[$index])) {
            $resource = $this->getCharges()[$index];
            if (!$lazy) {
                return $this->getHeidelpayObject()->getResourceService()->getResource($resource);
            }
            return $resource;
        }
        return null;
    }

    /**
     * @param Customer|string $customer
     *
     * @return Payment
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
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
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws HeidelpaySdkException
     */
    public function getPaymentType(): BasePaymentType
    {
        $paymentType = $this->paymentType;
        if (!$paymentType instanceof BasePaymentType) {
            throw new HeidelpaySdkException('The paymentType is not set.');
        }

        return $paymentType;
    }

    /**
     * @param BasePaymentType|string $paymentType
     *
     * @return Payment
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function setPaymentType($paymentType): Payment
    {
        if (empty($paymentType)) {
            throw new HeidelpaySdkException();
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
     * Return cancellation object in all cancellations of this payment object
     * i. e. refunds (charge cancellations) and reversals (authorize cancellations).
     *
     * @param $cancellationId
     *
     * @return Cancellation
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function getCancellation($cancellationId): Cancellation
    {
        /** @var Cancellation $cancellation */
        foreach ($this->getCancellations() as $cancellation) {
            if ($cancellation->getId() === $cancellationId) {
                return $cancellation;
            }
        }

        throw new HeidelpaySdkException();
    }

    /**
     * Return an array containing all cancellations of this payment object
     * i. e. refunds (charge cancellations) and reversals (authorize cancellations).
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
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
     * Return all shipments of the payment as array.
     *
     * @return array
     */
    public function getShipments(): array
    {
        return $this->shipments;
    }

    /**
     * Add shipment to shipment array.
     *
     * @param Shipment $shipment
     *
     * @return $this
     */
    public function addShipment(Shipment $shipment): self
    {
        $shipment->setPayment($this)->setParentResource($this);
        $this->shipments[] = $shipment;
        return $this;
    }

    /**
     * Return shipment object with the given id.
     *
     * @param string $shipmentId
     * @param bool   $lazy
     *
     * @return Shipment|null
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function getShipmentById($shipmentId, $lazy = false)
    {
        /** @var Shipment $shipment */
        foreach ($this->getShipments() as $shipment) {
            if ($shipment->getId() === $shipmentId) {
                if (!$lazy) {
                    $this->getHeidelpayObject()->getResourceService()->fetch($shipment);
                }
                return $shipment;
            }
        }

        return null;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->amount->getCurrency();
    }

    /**
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
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
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
     * Cancel payment/authorization object.
     *
     * @param float|null $amount
     *
     * @return Cancellation
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function cancel($amount = null): Cancellation
    {
        if ($this->getAuthorization() instanceof Authorization) {
            return $this->getHeidelpayObject()->cancelAuthorization($this->getAuthorization(), $amount);
        }

        throw new HeidelpaySdkException('This Payment has no Authorization. Please fetch the Payment first.');
    }

    /**
     * Charge a payment.
     *
     * @param null $amount
     * @param null $currency
     *
     * @return Charge
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function charge($amount = null, $currency = null): Charge
    {
        if ($this->getAuthorization(true) !== null) {
            return $this->getHeidelpayObject()->chargeAuthorization($this->getId(), $amount);
        }
        return $this->getHeidelpayObject()->chargePayment($this, $amount, $currency);
    }

    /**
     * Authorize a payment.
     *
     * @param float  $amount
     * @param string $currency
     * @param $paymentType
     * @param null $returnUrl
     * @param null $customer
     *
     * @return Authorization
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl = null, $customer = null): Authorization
    {
        $this->setPaymentType($paymentType);
        return $this->getHeidelpayObject()->authorizeWithPayment($amount, $currency, $this, $returnUrl, $customer);
    }

    /**
     * Perform ship transaction on the current payment.
     *
     * @return \heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface|Shipment
     *
     * @throws HeidelpayApiException
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     */
    public function ship()
    {
        return $this->getHeidelpayObject()->ship($this);
    }

    //</editor-fold>

    //<editor-fold desc="Transaction Update">

    /**
     * Create/update the authorization object of the given transaction.
     *
     * @param $transaction
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    private function updateAuthorizationTransaction($transaction)
    {
        $transactionId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'aut');
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
     * Create/update the charge object of the given transaction.
     *
     * @param $transaction
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    private function updateChargeTransaction($transaction)
    {
        $transactionId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'chg');
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
     * Create/update the cancel object of the given transaction.
     *
     * @param $transaction
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    private function updateReversalTransaction($transaction)
    {
        $transactionId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'cnl');
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            throw new HeidelpaySdkException('The Authorization object can not be found.');
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
     * Create/update the cancel object of the given transaction.
     *
     * @param $transaction
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    private function updateRefundTransaction($transaction)
    {
        $refundId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'cnl');
        $chargeId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'chg');

        $charge = $this->getChargeById($chargeId, true);
        if (!$charge instanceof Charge) {
            throw new HeidelpaySdkException('Charge object does not exist.');
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
     * Create/update the shipment object of the given transaction.
     *
     * @param $transaction
     *
     * @throws HeidelpaySdkException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    private function updateShipmentTransaction($transaction)
    {
        $shipmentId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'shp');
        $shipment = $this->getShipmentById($shipmentId, true);
        if (!$shipment instanceof Shipment) {
            $shipment = new Shipment(null, $shipmentId);
            $this->addShipment($shipment);
        }
        $shipment->setAmount($transaction->amount);
    }

    //</editor-fold>
}
