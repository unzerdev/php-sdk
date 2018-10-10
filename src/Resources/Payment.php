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

use heidelpay\MgwPhpSdk\Constants\TransactionTypes;
use heidelpay\MgwPhpSdk\Exceptions\MissingResourceException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Interfaces\PaymentInterface;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Traits\HasAmountsTrait;
use heidelpay\MgwPhpSdk\Traits\HasStateTrait;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

class Payment extends AbstractHeidelpayResource implements PaymentInterface
{
    use HasAmountsTrait;
    use HasStateTrait;

    //<editor-fold desc="Properties">
    /** @var string $redirectUrl */
    private $redirectUrl = '';

    /** @var Authorization $authorization */
    private $authorization;

    /** @var array $charges */
    private $charges = [];

    /** @var Customer $customer */
    private $customer;

    /** @var PaymentTypeInterface $paymentType */
    private $paymentType;

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
     * @param bool $lazy
     *
     * @return Authorization|AbstractHeidelpayResource|null
     */
    public function getAuthorization($lazy = false)
    {
        if (!$lazy) {
            return $this->getHeidelpayObject()->getResourceService()->getResource($this->authorization);
        }
        return $this->authorization;
    }

    /**
     * @param Authorization $authorize
     *
     * @return PaymentInterface
     */
    public function setAuthorization(Authorization $authorize): PaymentInterface
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
     *
     * @param string  $chargeId
     * @param boolean $lazy
     *
     * @return Charge
     *
     * @throws MissingResourceException
     */
    public function getChargeById($chargeId, $lazy = false): Charge
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
        throw new MissingResourceException();
    }

    /**
     * Get and return a Charge object by array index.
     *
     * @param int  $index
     * @param bool $lazy
     *
     * @return Charge
     *
     * @throws MissingResourceException
     */
    public function getCharge($index, $lazy = false): AbstractHeidelpayResource
    {
        if (isset($this->getCharges()[$index])) {
            $resource = $this->getCharges()[$index];
            if (!$lazy) {
                return $this->getHeidelpayObject()->getResourceService()->getResource($resource);
            }
            return $resource;
        }

        throw new MissingResourceException();
    }

    /**
     * @param Customer|string $customer
     *
     * @return Payment
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
     */
    public function getPaymentType(): PaymentTypeInterface
    {
        $paymentType = $this->paymentType;
        if (!$paymentType instanceof PaymentTypeInterface) {
            throw new MissingResourceException('The paymentType is not set.');
        }

        return $paymentType;
    }

    /**
     * @param PaymentTypeInterface|string $paymentType
     *
     * @return Payment
     */
    public function setPaymentType($paymentType): Payment
    {
        if (empty($paymentType)) {
            throw new MissingResourceException();
        }

        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();

        /** @var PaymentTypeInterface $paymentTypeObject */
        $paymentTypeObject = $paymentType;
        if (\is_string($paymentType)) {
            $paymentTypeObject = $heidelpay->fetchPaymentType($paymentType);
        } elseif ($paymentTypeObject instanceof PaymentTypeInterface) {
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
     * @throws MissingResourceException
     *
     * @return Cancellation
     */
    public function getCancellation($cancellationId): Cancellation
    {
        /** @var Cancellation $cancellation */
        foreach ($this->getCancellations() as $cancellation) {
            if ($cancellation->getId() === $cancellationId) {
                return $cancellation;
            }
        }

        throw new MissingResourceException();
    }

    /**
     * Return an array containing all cancellations of this payment object
     * i. e. refunds (charge cancellations) and reversals (authorize cancellations).
     *
     * @return array
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

    //</editor-fold>
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
     */
    public function handleResponse(\stdClass $response)
    {
        parent::handleResponse($response);

        // todo ggf. als object wie Address im customer
        if (isset($response->state->id)) {
            $this->setState($response->state->id);
        }

        // todo ggf. als object wie Address im customer
        if (isset($response->amount)) {
            $amount = $response->amount;

            if (isset($amount->total, $amount->charged, $amount->canceled, $amount->remaining)) {
                $this->setTotal($amount->total)
                    ->setCharged($amount->charged)
                    ->setCanceled($amount->canceled)
                    ->setRemaining($amount->remaining);
            }
        }

        if (isset($response->resources)) {
            $resources = $response->resources;

            // todo payment id ist wahrscheinlich die custom payment id die der händler vergibt und deshalb eigentlich keine resource.
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
                if (!$this->paymentType instanceof PaymentTypeInterface) {
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
     * todo: nicht jedes payment hat einen authorisierung
     */
    public function cancel($amount = null): Cancellation
    {
        if ($this->getAuthorization() instanceof Authorization) {
            return $this->getHeidelpayObject()->cancelAuthorization($this->getAuthorization(), $amount);
        }

        throw new MissingResourceException('This Payment has no Authorization. Please fetch the Payment first.');
    }

    /**
     * Charge a payment.
     *
     * @param null $amount
     * @param null $currency
     *
     * @return Charge
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
     */
    public function authorize($amount, $currency, $paymentType, $returnUrl = null, $customer = null): Authorization
    {
        $this->setPaymentType($paymentType);
        return $this->getHeidelpayObject()->authorizeWithPayment($amount, $currency, $this, $returnUrl, $customer);
    }

    //</editor-fold>

    //<editor-fold desc="Transaction Update">

    /**
     * @param $transaction
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
     * @param $transaction
     */
    private function updateChargeTransaction($transaction)
    {
        $transactionId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'chg');
        try {
            $charge = $this->getChargeById($transactionId, true);
        } catch (MissingResourceException $e) {
            $charge = (new Charge())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($transactionId);
            $this->addCharge($charge);
        }
        $charge->setAmount($transaction->amount);
    }

    /**
     * @param $transaction
     */
    private function updateReversalTransaction($transaction)
    {
        $transactionId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'cnl');
        $authorization = $this->getAuthorization(true);
        if (!$authorization instanceof Authorization) {
            throw new MissingResourceException();
        }

        try {
            $cancellation = $authorization->getCancellation($transactionId, true);
        } catch (MissingResourceException $e) {
            $cancellation =  (new Cancellation())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($transactionId);
            $authorization->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    /**
     * @param $transaction
     */
    private function updateRefundTransaction($transaction)
    {
        $refundId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'cnl');
        $chargeId = $this->getHeidelpayObject()->getResourceService()->getResourceId($transaction, 'chg');

        $charge = $this->getChargeById($chargeId);
        try {
            $cancellation = $charge->getCancellation($refundId);
        } catch (MissingResourceException $e) {
            $cancellation =  (new Cancellation())
                ->setPayment($this)
                ->setParentResource($this)
                ->setId($refundId);
            $charge->addCancellation($cancellation);
        }
        $cancellation->setAmount($transaction->amount);
    }

    //</editor-fold>
}
