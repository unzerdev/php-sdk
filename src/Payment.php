<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk;

use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;
use heidelpay\NmgPhpSdk\Traits\HasAmountsTrait;
use heidelpay\NmgPhpSdk\Traits\HasStateTrait;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class Payment extends AbstractHeidelpayResource implements PaymentInterface
{
    use HasAmountsTrait;
    use HasStateTrait;

    /** @var string $redirectUrl */
    private $redirectUrl = '';

    /** @var Authorization $authorize */
    private $authorize;

    /** @var array $charges */
    private $charges = [];

    /** @var Customer $customer */
    private $customer;

    /** @var PaymentTypeInterface $paymentType */
    private $paymentType;

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
    protected function handleResponse(\stdClass $response)
    {
        parent::handleResponse($response);

        if (isset($response->state->id)) {
            $this->setState($response->state->id);
        }

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

            if (isset($resources->paymentId)) {
                $this->setId($resources->paymentId);
            }

            if (isset($resources->customerId) && !empty($resources->customerId)) {
                if (!$this->customer instanceof Customer) {
                    $this->customer = $this->getHeidelpayObject()->fetchCustomerById($resources->customerId);
                } else {
                    $this->customer->fetch();
                }
            }

            if (isset($resources->typeId) && !empty($resources->typeId)) {
                if (!$this->paymentType instanceof PaymentTypeInterface) {
                    $this->paymentType = $this->getHeidelpayObject()->fetchPaymentType($resources->typeId);
                }
            }
        }

    }
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
     * @return Payment
     */
    public function setRedirectUrl(string $redirectUrl): Payment
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    /**
     * @return Authorization|null
     */
    public function getAuthorization()
    {
        return $this->authorize;
    }

    /**
     * @param Authorization $authorize
     * @return PaymentInterface
     */
    public function setAuthorization(Authorization $authorize): PaymentInterface
    {
        $authorize->setPayment($this);
        $authorize->setParentResource($this);
        $this->authorize = $authorize;
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
     * @return Payment
     */
    public function setCharges(array $charges): Payment
    {
        $this->charges = $charges;
        return $this;
    }

    /**
     * @param Charge $charge
     */
    public function addCharge(Charge $charge)
    {
        $this->charges[$charge->getId()] = $charge;
    }

    /**
     * @param Customer $customer
     * @return Payment
     */
    public function setCustomer(Customer $customer): Payment
    {
        $customer->setParentResource($this->getHeidelpayObject());
        $this->customer = $customer;
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
     * @return Customer
     */
    public function createCustomer(): Customer
    {
        $this->customer = new Customer($this);
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
     * @param PaymentTypeInterface $paymentType
     * @return Payment
     */
    public function setPaymentType(PaymentTypeInterface $paymentType): Payment
    {
        $this->paymentType = $paymentType;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="TransactionTypes">

    /**
     * {@inheritDoc}
     *
     * todo: this should be handled by the api.
     */
    public function fullCharge(): Charge
    {
        // todo: authorization muss erst geholt werden
        if (!$this->getAuthorization() instanceof Authorization) {
            throw new MissingResourceException('Cannot perform full charge without authorization.');
        }

        // charge amount
        return $this->charge($this->getRemaining());
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @param Customer|null $customer
     * @return Charge
     */
    public function charge($amount = null, $currency = null, $returnUrl = null, $customer = null): Charge
    {
        if (!$this->getPaymentType()->isChargeable()) {
            throw new IllegalTransactionTypeException(__METHOD__);
        }

        if ($amount === null) {
            return $this->fullCharge();
        }

        if ($customer instanceof Customer) {
            $this->setCustomer($customer);
        }

        /** @var Charge $charge */
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setParentResource($this)
            ->setPayment($this)
            ->create();
        // needs to be set after creation to use id as key in charge array
        $this->addCharge($charge);

        return $charge;
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl): Authorization
    {
        if (!$this->getPaymentType()->isAuthorizable()) {
            throw new IllegalTransactionTypeException(__METHOD__);
        }

        $authorization = new Authorization($amount, $currency, $returnUrl);
        $this->setAuthorization($authorization);
        $authorization->create();

        return $authorization;
    }

    /**
     * Sets the given paymentType and performs an authorization.
     *
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param PaymentTypeInterface $paymentType
     * @return Authorization
     */
    public function authorizeWithPaymentType($amount, $currency, $returnUrl, PaymentTypeInterface $paymentType): Authorization
    {
        return $this->setPaymentType($paymentType)->authorize($amount, $currency, $returnUrl);
    }

    /**
     * Perform a full cancel on the payment.
     * Returns the payment object itself.
     * Cancellation-Object is not returned since on cancelling might affect several charges thus creates several
     * Cancellation-Objects in one go.
     *
     * @return PaymentInterface
     */
    public function fullCancel(): PaymentInterface
    {
        if ($this->authorize instanceof Authorization && !$this->isCompleted()) {
            $this->authorize->cancel();
            return $this;
        }

        $this->cancelAllCharges();

        return $this;
    }

    /**
     * @param float $amount
     * @return PaymentInterface
     */
    public function cancel($amount = null): PaymentInterface
    {
        if (null === $amount) {
            return $this->fullCancel();
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function cancelAllCharges()
    {
        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            $charge->cancel();
        }
    }
    //</editor-fold>
}
