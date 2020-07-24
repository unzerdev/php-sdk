<?php
/**
 * This is the heidelpay object which is the base object providing all functionalities needed to
 * access the api.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP
 */
namespace heidelpayPHP;

use DateTime;
use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Interfaces\CancelServiceInterface;
use heidelpayPHP\Interfaces\DebugHandlerInterface;
use heidelpayPHP\Interfaces\HeidelpayParentInterface;
use heidelpayPHP\Interfaces\PaymentServiceInterface;
use heidelpayPHP\Interfaces\ResourceServiceInterface;
use heidelpayPHP\Interfaces\WebhookServiceInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\InstalmentPlans;
use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\Recurring;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Services\CancelService;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\PaymentService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\WebhookService;
use heidelpayPHP\Validators\PrivateKeyValidator;
use RuntimeException;

class Heidelpay implements HeidelpayParentInterface, PaymentServiceInterface, ResourceServiceInterface, WebhookServiceInterface, CancelServiceInterface
{
    public const BASE_URL = 'api.heidelpay.com';
    public const API_VERSION = 'v1';
    public const SDK_TYPE = 'heidelpayPHP';
    public const SDK_VERSION = '1.2.8.0';

    /** @var string $key */
    private $key;

    /** @var string $locale */
    private $locale;

    /** @var ResourceServiceInterface $resourceService */
    private $resourceService;

    /** @var PaymentServiceInterface $paymentService */
    private $paymentService;

    /** @var WebhookServiceInterface $webhookService */
    private $webhookService;

    /** @var CancelServiceInterface $cancelService */
    private $cancelService;

    /** @var HttpService $httpService */
    private $httpService;

    /** @var DebugHandlerInterface $debugHandler */
    private $debugHandler;

    /** @var boolean $debugMode */
    private $debugMode = false;

    /**
     * Construct a new heidelpay object.
     *
     * @param string $key    The private key your received from your heidelpay contact person.
     * @param string $locale The locale of the customer defining defining the translation (e.g. 'en-GB' or 'de-DE').
     *
     * @link https://docs.heidelpay.com/docs/web-integration#section-localization-and-languages
     *
     * @throws RuntimeException A RuntimeException will be thrown if the key is not of type private.
     */
    public function __construct($key, $locale = null)
    {
        $this->setKey($key);
        $this->setLocale($locale);

        $this->resourceService = new ResourceService($this);
        $this->paymentService  = new PaymentService($this);
        $this->webhookService  = new WebhookService($this);
        $this->cancelService   = new CancelService($this);
        $this->httpService     = new HttpService();
    }

    //<editor-fold desc="Getters/Setters">

    /**
     * Returns the set private key used to connect to the API.
     *
     * @return string The key that is currently set.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets your private key used to connect to the API.
     *
     * @param string $key The private key.
     *
     * @return Heidelpay This heidelpay object.
     *
     * @throws RuntimeException Throws a RuntimeException when the key is invalid.
     */
    public function setKey($key): Heidelpay
    {
        if (!PrivateKeyValidator::validate($key)) {
            throw new RuntimeException('Illegal key: Use a valid private key with this SDK!');
        }

        $this->key = $key;
        return $this;
    }

    /**
     * Returns the set customer locale.
     *
     * @return string|null The locale of the customer.
     *                     Refer to the documentation under https://docs.heidelpay.com for a list of supported values.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets the customer locale.
     *
     * @param string|null $locale The customer locale to set.
     *                            Ref. https://docs.heidelpay.com for a list of supported values.
     *
     * @return Heidelpay This heidelpay object.
     */
    public function setLocale($locale): Heidelpay
    {
        $this->locale = str_replace('_', '-', $locale);
        return $this;
    }

    /**
     * @param ResourceService $resourceService
     *
     * @return Heidelpay
     */
    public function setResourceService(ResourceService $resourceService): Heidelpay
    {
        $this->resourceService = $resourceService->setHeidelpay($this);
        return $this;
    }

    /**
     * Returns the ResourceService object.
     *
     * @return ResourceService The resource service object of this heidelpay instance.
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * @param PaymentService $paymentService
     *
     * @return Heidelpay
     */
    public function setPaymentService(PaymentService $paymentService): Heidelpay
    {
        $this->paymentService = $paymentService->setHeidelpay($this);
        return $this;
    }

    /**
     * @return PaymentServiceInterface
     */
    public function getPaymentService(): PaymentServiceInterface
    {
        return $this->paymentService;
    }

    /**
     * @return WebhookServiceInterface
     */
    public function getWebhookService(): WebhookServiceInterface
    {
        return $this->webhookService;
    }

    /**
     * @param WebhookServiceInterface $webhookService
     *
     * @return Heidelpay
     */
    public function setWebhookService(WebhookServiceInterface $webhookService): Heidelpay
    {
        $this->webhookService = $webhookService;
        return $this;
    }

    /**
     * @return CancelServiceInterface
     */
    public function getCancelService(): CancelServiceInterface
    {
        return $this->cancelService;
    }

    /**
     * @param CancelService $cancelService
     *
     * @return Heidelpay
     */
    public function setCancelService(CancelService $cancelService): Heidelpay
    {
        $this->cancelService = $cancelService->setHeidelpay($this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Enable debug output.
     * You need to setter inject a custom handler implementing the DebugOutputHandlerInterface via
     * Heidelpay::setDebugHandler() for this to work.
     *
     * @param bool $debugMode
     *
     * @return Heidelpay
     */
    public function setDebugMode(bool $debugMode): Heidelpay
    {
        $this->debugMode = $debugMode;
        return $this;
    }

    /**
     * @return DebugHandlerInterface|null
     */
    public function getDebugHandler(): ?DebugHandlerInterface
    {
        return $this->debugHandler;
    }

    /**
     * Use this method to inject a custom handler for debug messages from the http-adapter.
     * Remember to enable debug output using Heidelpay::setDebugMode(true).
     *
     * @param DebugHandlerInterface $debugHandler
     *
     * @return Heidelpay
     */
    public function setDebugHandler(DebugHandlerInterface $debugHandler): Heidelpay
    {
        $this->debugHandler = $debugHandler;
        return $this;
    }

    /**
     * @return HttpService
     */
    public function getHttpService(): HttpService
    {
        return $this->httpService;
    }

    /**
     * @param HttpService $httpService
     *
     * @return Heidelpay
     */
    public function setHttpService(HttpService $httpService): Heidelpay
    {
        $this->httpService = $httpService;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="ParentIF">

    /**
     * Returns this heidelpay instance.
     *
     * @return Heidelpay This heidelpay object.
     */
    public function getHeidelpayObject(): Heidelpay
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri($appendId = true): string
    {
        return '';
    }

    //</editor-fold>

    //<editor-fold desc="Resources">

    /**
     * Updates the given local resource object if it has not been fetched before.
     * If you are looking to update a local resource even if it has been fetched before please call fetchResource().
     *
     * @param AbstractHeidelpayResource $resource The local resource object to update.
     *
     * @return AbstractHeidelpayResource The updated resource object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @deprecated since 1.2.6.0
     */
    public function getResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        return $this->resourceService->getResource($resource);
    }

    /**
     * Updates the given local resource object.
     *
     * @param AbstractHeidelpayResource $resource The local resource object to update.
     *
     * @return AbstractHeidelpayResource The updated resource object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @deprecated since 1.2.6.0
     */
    public function fetchResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchResource($resource);
    }

    //<editor-fold desc="Recurring">

    /**
     * {@inheritDoc}
     */
    public function activateRecurringPayment($paymentType, $returnUrl): Recurring
    {
        return $this->resourceService->activateRecurringPayment($paymentType, $returnUrl);
    }

    //</editor-fold>

    //<editor-fold desc="Payment resource">

    /**
     * {@inheritDoc}
     */
    public function fetchPayment($payment): Payment
    {
        return $this->resourceService->fetchPayment($payment);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchPaymentByOrderId($orderId): Payment
    {
        return $this->resourceService->fetchPaymentByOrderId($orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Keypair resource">

    /**
     * {@inheritDoc}
     */
    public function fetchKeypair($detailed = false): Keypair
    {
        return $this->resourceService->fetchKeypair($detailed);
    }

    //</editor-fold>

    //<editor-fold desc="Metadata resource">

    /**
     * {@inheritDoc}
     */
    public function createMetadata(Metadata $metadata): Metadata
    {
        return $this->resourceService->createMetadata($metadata);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchMetadata($metadata): Metadata
    {
        return $this->resourceService->fetchMetadata($metadata);
    }

    //</editor-fold>

    //<editor-fold desc="Basket resource">

    /**
     * {@inheritDoc}
     */
    public function createBasket(Basket $basket): Basket
    {
        return $this->resourceService->createBasket($basket);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBasket($basket): Basket
    {
        return $this->resourceService->fetchBasket($basket);
    }

    /**
     * {@inheritDoc}
     */
    public function updateBasket(Basket $basket): Basket
    {
        return $this->resourceService->updateBasket($basket);
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType resource">

    /**
     * {@inheritDoc}
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        return $this->resourceService->createPaymentType($paymentType);
    }

    /**
     * {@inheritDoc}
     */
    public function updatePaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        return $this->resourceService->updatePaymentType($paymentType);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchPaymentType($typeId): BasePaymentType
    {
        return $this->resourceService->fetchPaymentType($typeId);
    }

    //</editor-fold>

    //<editor-fold desc="Customer resource">

    /**
     * {@inheritDoc}
     */
    public function createCustomer(Customer $customer): Customer
    {
        return $this->resourceService->createCustomer($customer);
    }

    /**
     * {@inheritDoc}
     */
    public function createOrUpdateCustomer(Customer $customer): Customer
    {
        return $this->resourceService->createOrUpdateCustomer($customer);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchCustomer($customer): Customer
    {
        return $this->resourceService->fetchCustomer($customer);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchCustomerByExtCustomerId($customerId): Customer
    {
        return $this->resourceService->fetchCustomerByExtCustomerId($customerId);
    }

    /**
     * {@inheritDoc}
     */
    public function updateCustomer(Customer $customer): Customer
    {
        return $this->resourceService->updateCustomer($customer);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCustomer($customer): void
    {
        $this->resourceService->deleteCustomer($customer);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization resource">

    /**
     * {@inheritDoc}
     */
    public function fetchAuthorization($payment): Authorization
    {
        return $this->resourceService->fetchAuthorization($payment);
    }

    //</editor-fold>

    //<editor-fold desc="Charge resource">

    /**
     * {@inheritDoc}
     */
    public function fetchChargeById($paymentId, $chargeId): Charge
    {
        return $this->resourceService->fetchChargeById($paymentId, $chargeId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchCharge(Charge $charge): Charge
    {
        return $this->resourceService->fetchCharge($charge);
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation resource">

    /**
     * {@inheritDoc}
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): Cancellation
    {
        return $this->resourceService->fetchReversalByAuthorization($authorization, $cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchReversal($payment, $cancellationId): Cancellation
    {
        return $this->resourceService->fetchReversal($payment, $cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchRefundById($payment, $chargeId, $cancellationId): Cancellation
    {
        return $this->resourceService->fetchRefundById($payment, $chargeId, $cancellationId);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchRefund(Charge $charge, $cancellationId): Cancellation
    {
        return $this->resourceService->fetchRefund($charge, $cancellationId);
    }

    //</editor-fold>

    //<editor-fold desc="Shipment resource">

    /**
     * {@inheritDoc}
     */
    public function fetchShipment($payment, $shipmentId): Shipment
    {
        return $this->resourceService->fetchShipment($payment, $shipmentId);
    }

    //</editor-fold>

    //<editor-fold desc="Payout resource">

    /**
     * {@inheritDoc}
     */
    public function fetchPayout($payment): Payout
    {
        return $this->resourceService->fetchPayout($payment);
    }

    //</editor-fold>

    //<editor-fold desc="Webhook resource">

    /**
     * {@inheritDoc}
     */
    public function createWebhook(string $url, string $event): Webhook
    {
        return $this->webhookService->createWebhook($url, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchWebhook($webhook): Webhook
    {
        return $this->webhookService->fetchWebhook($webhook);
    }

    /**
     * {@inheritDoc}
     */
    public function updateWebhook($webhook): Webhook
    {
        return $this->webhookService->updateWebhook($webhook);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteWebhook($webhook)
    {
        return $this->webhookService->deleteWebhook($webhook);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllWebhooks(): array
    {
        return $this->webhookService->fetchAllWebhooks();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAllWebhooks(): void
    {
        $this->webhookService->deleteAllWebhooks();
    }

    /**
     * {@inheritDoc}
     */
    public function registerMultipleWebhooks(string $url, array $events): array
    {
        return $this->webhookService->registerMultipleWebhooks($url, $events);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchResourceFromEvent($eventJson = null): AbstractHeidelpayResource
    {
        return $this->webhookService->fetchResourceFromEvent($eventJson);
    }

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Transactions">

    //<editor-fold desc="Authorize transactions">

    /**
     * {@inheritDoc}
     */
    public function authorize(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null,
        $invoiceId = null,
        $referenceText = null
    ): Authorization {
        return $this->paymentService->authorize(
            $amount,
            $currency,
            $paymentType,
            $returnUrl,
            $customer,
            $orderId,
            $metadata,
            $basket,
            $card3ds,
            $invoiceId,
            $referenceText
        );
    }

    //</editor-fold>

    //<editor-fold desc="Charge transactions">

    /**
     * {@inheritDoc}
     */
    public function charge(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $card3ds = null,
        $invoiceId = null,
        $paymentReference = null
    ): Charge {
        return $this->paymentService->charge(
            $amount,
            $currency,
            $paymentType,
            $returnUrl,
            $customer,
            $orderId,
            $metadata,
            $basket,
            $card3ds,
            $invoiceId,
            $paymentReference
        );
    }

    /**
     * {@inheritDoc}
     */
    public function chargeAuthorization(
        $payment,
        float $amount = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge {
        return $this->paymentService->chargeAuthorization($payment, $amount, $orderId, $invoiceId);
    }

    /**
     * {@inheritDoc}
     */
    public function chargePayment(
        $payment,
        float $amount = null,
        string $currency = null,
        string $orderId = null,
        string $invoiceId = null
    ): Charge {
        $paymentObject = $this->resourceService->getPaymentResource($payment);
        return $this->paymentService->chargePayment($paymentObject, $amount, $currency, $orderId, $invoiceId);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization Cancel/Reversal">

    /**
     * {@inheritDoc}
     */
    public function cancelAuthorization(Authorization $authorization, float $amount = null): Cancellation
    {
        return $this->cancelService->cancelAuthorization($authorization, $amount);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelAuthorizationByPayment($payment, float $amount = null): Cancellation
    {
        return $this->cancelService->cancelAuthorizationByPayment($payment, $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Payment Cancel">

    /**
     * {@inheritDoc}
     */
    public function cancelPayment(
        $payment,
        float $amount = null,
        $reasonCode = CancelReasonCodes::REASON_CODE_CANCEL,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): array {
        return $this->cancelService
            ->cancelPayment($payment, $amount, $reasonCode, $referenceText, $amountNet, $amountVat);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelPaymentAuthorization($payment, float $amount = null): ?Cancellation
    {
        return $this->cancelService->cancelPaymentAuthorization($payment, $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Charge Cancel/Refund">

    /**
     * {@inheritDoc}
     */
    public function cancelChargeById(
        $payment,
        string $chargeId,
        float $amount = null,
        string $reasonCode = null,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        return $this->cancelService
            ->cancelChargeById($payment, $chargeId, $amount, $reasonCode, $referenceText, $amountNet, $amountVat);
    }

    /**
     * {@inheritDoc}
     */
    public function cancelCharge(
        Charge $charge,
        float $amount = null,
        string $reasonCode = null,
        string $referenceText = null,
        float $amountNet = null,
        float $amountVat = null
    ): Cancellation {
        return $this->cancelService
            ->cancelCharge($charge, $amount, $reasonCode, $referenceText, $amountNet, $amountVat);
    }

    //</editor-fold>

    //<editor-fold desc="Shipment transactions">

    /**
     * {@inheritDoc}
     */
    public function ship($payment, string $invoiceId = null, string $orderId = null): Shipment
    {
        return $this->paymentService->ship($payment, $invoiceId, $orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Payout transactions">

    /**
     * {@inheritDoc}
     */
    public function payout(
        $amount,
        $currency,
        $paymentType,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $invoiceId = null,
        $referenceText = null
    ): Payout {
        return $this->paymentService->payout(
            $amount,
            $currency,
            $paymentType,
            $returnUrl,
            $customer,
            $orderId,
            $metadata,
            $basket,
            $invoiceId,
            $referenceText
        );
    }

    //</editor-fold>

    //<editor-fold desc="PayPage">

    /**
     * {@inheritDoc}
     */
    public function initPayPageCharge(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->paymentService->initPayPageCharge($paypage, $customer, $basket, $metadata);
    }

    /**
     * {@inheritDoc}
     */
    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->paymentService->initPayPageAuthorize($paypage, $customer, $basket, $metadata);
    }

    //</editor-fold>

    //<editor-fold desc="Hire Purchase (FlexiPay Rate)">

    /**
     * {@inheritDoc}
     */
    public function fetchDirectDebitInstalmentPlans(
        $amount,
        $currency,
        $effectiveInterest,
        DateTime $orderDate = null
    ): InstalmentPlans {
        return $this->paymentService
            ->fetchDirectDebitInstalmentPlans($amount, $currency, $effectiveInterest, $orderDate);
    }

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Writes the given string to the registered debug handler if debug mode is enabled.
     *
     * @param $message
     */
    public function debugLog($message): void
    {
        if ($this->isDebugMode()) {
            $debugHandler = $this->getDebugHandler();
            if ($debugHandler instanceof DebugHandlerInterface) {
                $debugHandler->log('(' . getmypid() . ') ' . $message);
            }
        }
    }

    //</editor-fold>
}
