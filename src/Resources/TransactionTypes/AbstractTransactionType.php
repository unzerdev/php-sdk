<?php
/**
 * This is the base class for all transaction types.
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
 * @package  UnzerSDK\TransactionTypes
 */
namespace UnzerSDK\Resources\TransactionTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Traits\HasAdditionalTransactionData;
use UnzerSDK\Traits\HasCustomerMessage;
use UnzerSDK\Traits\HasDate;
use UnzerSDK\Traits\HasOrderId;
use UnzerSDK\Traits\HasStates;
use UnzerSDK\Traits\HasTraceId;
use UnzerSDK\Traits\HasUniqueAndShortId;
use RuntimeException;
use stdClass;

abstract class AbstractTransactionType extends AbstractUnzerResource
{
    use HasOrderId;
    use HasStates;
    use HasUniqueAndShortId;
    use HasTraceId;
    use HasCustomerMessage;
    use HasAdditionalTransactionData;
    use HasDate;

    //<editor-fold desc="Properties">

    /** @var Payment $payment */
    private $payment;
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">

    /**
     * Return the payment property.
     *
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * Set the payment object property.
     *
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment($payment): self
    {
        $this->payment = $payment;
        $this->setParentResource($payment);
        return $this;
    }

    /**
     * Return the Id of the referenced payment object.
     *
     * @return null|string The Id of the payment object or null if nothing is found.
     */
    public function getPaymentId(): ?string
    {
        if ($this->payment instanceof Payment) {
            return $this->payment->getId();
        }

        return null;
    }

    /**
     * Return the redirect url stored in the payment object.
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->payment->getRedirectUrl();
    }

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    /**
     * {@inheritDoc}
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function handleResponse(stdClass $response, $method = HttpAdapterInterface::REQUEST_GET): void
    {
        parent::handleResponse($response, $method);

        /** @var Payment $payment */
        $payment = $this->getPayment();
        if (isset($response->resources->paymentId)) {
            $payment->setId($response->resources->paymentId);
        }

        if (isset($response->redirectUrl)) {
            $payment->handleResponse((object)['redirectUrl' => $response->redirectUrl]);
        }

        if (isset($response->additionalTransactionData)) {
            $this->setAdditionalTransactionData($response->additionalTransactionData);
        }

        if ($method !== HttpAdapterInterface::REQUEST_GET) {
            $this->fetchPayment();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function getLinkedResources(): array
    {
        /** @var Payment $payment */
        $payment = $this->getPayment();
        $paymentType = $payment ? $payment->getPaymentType() : null;
        if (!$paymentType instanceof BasePaymentType) {
            throw new RuntimeException('Payment type is missing!');
        }

        return [
            'customer'=> $payment->getCustomer(),
            'type' => $paymentType,
            'metadata' => $payment->getMetadata(),
            'basket' => $payment->getBasket()
        ];
    }

    //</editor-fold>

    /**
     * Updates the referenced payment object if it exists and if this is not the payment object itself.
     * This is called from the crud methods to update the payments state whenever anything happens.
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fetchPayment(): void
    {
        $payment = $this->getPayment();
        if ($payment instanceof AbstractUnzerResource) {
            $this->fetchResource($payment);
        }
    }
}
