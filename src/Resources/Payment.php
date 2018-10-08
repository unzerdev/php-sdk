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
 *
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

    /** @var Authorization $authorize */
    private $authorize;

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
     * @param Customer|string $customer
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
     * @param PaymentTypeInterface $paymentType
     * @return Payment
     */
    public function setPaymentType(PaymentTypeInterface $paymentType): Payment
    {
        $this->paymentType = $paymentType;
        return $this;
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
                        $transactionId = $this->getTransactionId($transaction, 'aut');
                        // todo: refactor
                        $authorization = $this->getAuthorization();
                        if (!$authorization instanceof Authorization) {
                            $authorization = (new Authorization())
                                ->setPayment($this)
                                ->setParentResource($this)
                                ->setId($transactionId);
                            $this->setAuthorization($authorization);
                        }
                        $authorization->setAmount($transaction->amount);
                        break;
                    case TransactionTypes::CHARGE:
                        // todo: like auth
                        break;
                    case TransactionTypes::CANCEL:
                        // todo: like auth
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

    /**
     * @param $transaction
     * @param $pattern
     * @return mixed
     */
    protected function getTransactionId($transaction, $pattern)
    {
        $matches = [];
        preg_match('~\/([s|p]{1}-' . $pattern . '-[\d]+)~', $transaction->url, $matches);

        if (\count($matches) < 2) {
            throw new \RuntimeException('Id not found!');
        }

        return $matches[1];
    }
    //</editor-fold>
}
