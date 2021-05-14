<?php
/**
 * This is the Unzer object which is the base object providing all functionalities needed to
 * access the api.
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
 * @package  UnzerSDK
 */
namespace UnzerSDK;

use DateTime;
use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Constants\CancelReasonCodes;
use UnzerSDK\Interfaces\CancelServiceInterface;
use UnzerSDK\Interfaces\DebugHandlerInterface;
use UnzerSDK\Interfaces\UnzerParentInterface;
use UnzerSDK\Interfaces\PaymentServiceInterface;
use UnzerSDK\Interfaces\ResourceServiceInterface;
use UnzerSDK\Interfaces\WebhookServiceInterface;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\InstalmentPlans;
use UnzerSDK\Resources\Keypair;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Paypage;
use UnzerSDK\Resources\Recurring;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Payout;
use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\Resources\Webhook;
use UnzerSDK\Services\CancelService;
use UnzerSDK\Services\HttpService;
use UnzerSDK\Services\PaymentService;
use UnzerSDK\Services\ResourceService;
use UnzerSDK\Services\WebhookService;
use UnzerSDK\Validators\PrivateKeyValidator;
use RuntimeException;

class Unzer implements UnzerParentInterface, PaymentServiceInterface, ResourceServiceInterface, WebhookServiceInterface, CancelServiceInterface
{
    public const BASE_URL = 'api.unzer.com';
    public const API_VERSION = 'v1';
    public const SDK_TYPE = 'UnzerPHP';
    public const SDK_VERSION = '1.1.2.0';

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
     * Construct a new Unzer object.
     *
     * @param string $key    The private key your received from your Unzer contact person.
     * @param string $locale The locale of the customer defining defining the translation (e.g. 'en-GB' or 'de-DE').
     *
     * @link https://docs.unzer.com/integrate/web-integration/#section-localization-and-languages
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
     * @return Unzer This Unzer object.
     *
     * @throws RuntimeException Throws a RuntimeException when the key is invalid.
     */
    public function setKey($key): Unzer
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
     *                     Refer to the documentation under https://docs.unzer.com for a list of supported values.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets the customer locale.
     *
     * @param string|null $locale The customer locale to set.
     *                            Ref. https://docs.unzer.com for a list of supported values.
     *
     * @return Unzer This Unzer object.
     */
    public function setLocale($locale): Unzer
    {
        $this->locale = str_replace('_', '-', $locale);
        return $this;
    }

    /**
     * @param ResourceService $resourceService
     *
     * @return Unzer
     */
    public function setResourceService(ResourceService $resourceService): Unzer
    {
        $this->resourceService = $resourceService->setUnzer($this);
        return $this;
    }

    /**
     * Returns the ResourceService object.
     *
     * @return ResourceService The resource service object of this Unzer instance.
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * @param PaymentService $paymentService
     *
     * @return Unzer
     */
    public function setPaymentService(PaymentService $paymentService): Unzer
    {
        $this->paymentService = $paymentService->setUnzer($this);
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
     * @return Unzer
     */
    public function setWebhookService(WebhookServiceInterface $webhookService): Unzer
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
     * @return Unzer
     */
    public function setCancelService(CancelService $cancelService): Unzer
    {
        $this->cancelService = $cancelService->setUnzer($this);
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
     * Unzer::setDebugHandler() for this to work.
     *
     * @param bool $debugMode
     *
     * @return Unzer
     */
    public function setDebugMode(bool $debugMode): Unzer
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
     * Remember to enable debug output using Unzer::setDebugMode(true).
     *
     * @param DebugHandlerInterface $debugHandler
     *
     * @return Unzer
     */
    public function setDebugHandler(DebugHandlerInterface $debugHandler): Unzer
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
     * @return Unzer
     */
    public function setHttpService(HttpService $httpService): Unzer
    {
        $this->httpService = $httpService;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="ParentIF">

    /**
     * Returns this Unzer instance.
     *
     * @return Unzer This Unzer object.
     */
    public function getUnzerObject(): Unzer
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri($appendId = true, $httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return '';
    }

    //</editor-fold>

    //<editor-fold desc="Resources">

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
    public function fetchResourceFromEvent($eventJson = null): AbstractUnzerResource
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

    //<editor-fold desc="HInstallment Secured">

    /**
     * {@inheritDoc}
     */
    public function fetchInstallmentPlans(
        $amount,
        $currency,
        $effectiveInterest,
        DateTime $orderDate = null
    ): InstalmentPlans {
        return $this->paymentService
            ->fetchInstallmentPlans($amount, $currency, $effectiveInterest, $orderDate);
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
