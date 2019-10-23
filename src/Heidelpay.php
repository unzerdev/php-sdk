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

use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Interfaces\DebugHandlerInterface;
use heidelpayPHP\Interfaces\HeidelpayParentInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\TransactionTypes\AbstractTransactionType;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Resources\Webhook;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\PaymentService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\Services\WebhookService;
use heidelpayPHP\Validators\PrivateKeyValidator;
use RuntimeException;

class Heidelpay implements HeidelpayParentInterface
{
    const BASE_URL = 'api.heidelpay.com';
    const API_VERSION = 'v1';
    const SDK_TYPE = 'HeidelpayPHP';
    const SDK_VERSION = '1.2.3.0';

    /** @var string $key */
    private $key;

    /** @var string $locale */
    private $locale;

    /** @var ResourceService $resourceService */
    private $resourceService;

    /** @var PaymentService $paymentService */
    private $paymentService;

    /** @var WebhookService $webhookService */
    private $webhookService;

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
     * @param string $locale The locale of the customer defining defining the translation.
     *
     * @throws RuntimeException A RuntimeException will be thrown if the key is not of type private.
     */
    public function __construct($key, $locale = null)
    {
        $this->setKey($key);
        $this->locale = $locale;

        $this->resourceService = new ResourceService($this);
        $this->paymentService  = new PaymentService($this);
        $this->webhookService  = new WebhookService($this);
        $this->httpService = new HttpService();
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the customer locale.
     *
     * @param string $locale The customer locale to set.
     *                       Refer to the documentation under https://docs.heidelpay.com for a list of supported values.
     *
     * @return Heidelpay This heidelpay object.
     */
    public function setLocale($locale): Heidelpay
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param ResourceService $resourceService
     *
     * @return Heidelpay
     */
    public function setResourceService(ResourceService $resourceService): Heidelpay
    {
        $this->resourceService = $resourceService;
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
        $this->paymentService = $paymentService;
        return $this;
    }

    /**
     * @return PaymentService
     */
    public function getPaymentService(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * @return WebhookService
     */
    public function getWebhookService(): WebhookService
    {
        return $this->webhookService;
    }

    /**
     * @param WebhookService $webhookService
     *
     * @return Heidelpay
     */
    public function setWebhookService(WebhookService $webhookService): Heidelpay
    {
        $this->webhookService = $webhookService;
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
     * You need to setter inject a custom handler implementing the DebugOutputHandlerInterface via setDebugHandler
     * for this to work.
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
    public function getDebugHandler()
    {
        return $this->debugHandler;
    }

    /**
     * Use this method to inject a custom handler for debug messages form the http-adapter.
     * Remember to enable debug output by setting the constant Heidelpay::DEBUG_MODE true.
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
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
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
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchResource(AbstractHeidelpayResource $resource): AbstractHeidelpayResource
    {
        return $this->resourceService->fetch($resource);
    }

    //<editor-fold desc="Recurring">

    /**
     * Activate recurring payment for the given payment type (if possible).
     *
     * @param string|BasePaymentType $paymentType The payment to activate recurring payment for.
     * @param string                 $returnUrl   The URL to which the customer gets redirected in case of a 3ds transaction
     *
     * @return mixed
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function activateRecurringPayment($paymentType, $returnUrl)
    {
        return $this->resourceService->createRecurring($paymentType, $returnUrl);
    }

    //</editor-fold>

    //<editor-fold desc="Payment resource">

    /**
     * Updates the given payment payment object.
     *
     * @param Payment|string $payment The local payment object to be updated.
     *
     * @return Payment Returns the updated payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchPayment($payment): Payment
    {
        return $this->resourceService->fetchPayment($payment);
    }

    /**
     * Fetches a payment object using its orderId.
     *
     * @param string $orderId The orderId set during authorize or charge.
     *
     * @return Payment Returns the updated payment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchPaymentByOrderId($orderId): Payment
    {
        return $this->resourceService->fetchPaymentByOrderId($orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Keypair resource">

    /**
     * Read and return the public key and configured payment types from API.
     *
     * @param bool $detailed If this flag is set detailed information are fetched.
     *
     * @return Keypair The Keypair object composed of the data returned by the API.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchKeypair($detailed = false): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchKeypair($detailed);
    }

    //</editor-fold>

    //<editor-fold desc="Metadata resource">

    /**
     * Create Metadata resource.
     *
     * @param Metadata $metadata The Metadata object to be created.
     *
     * @return Metadata The fetched Metadata resource.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createMetadata($metadata): Metadata
    {
        return $this->resourceService->createMetadata($metadata);
    }

    /**
     * Fetch and return Metadata resource.
     *
     * @param Metadata|string $metadata The local Metadata object to be fetched.
     *
     * @return Metadata The fetched Metadata resource.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchMetadata($metadata): Metadata
    {
        return $this->resourceService->fetchMetadata($metadata);
    }

    //</editor-fold>

    //<editor-fold desc="Basket resource">

    /**
     * Creates and returns the given basket resource.
     *
     * @param Basket $basket The basket to be created.
     *
     * @return Basket The created Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createBasket(Basket $basket): Basket
    {
        return $this->resourceService->createBasket($basket);
    }

    /**
     * Fetches and returns the given Basket (by object or id).
     *
     * @param Basket|string $basket Basket object or id of basket to be fetched.
     *
     * @return Basket The fetched Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchBasket($basket): Basket
    {
        return $this->resourceService->fetchBasket($basket);
    }

    /**
     * Update the a basket resource with the given basket object (id must be set).
     *
     * @param Basket $basket
     *
     * @return Basket The updated Basket object.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function updateBasket(Basket $basket): Basket
    {
        return $this->resourceService->updateBasket($basket);
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType resource">

    /**
     * Creates a PaymentType resource from the given PaymentType object.
     * This is used to create the payment object prior to any transaction.
     * Usually this will be done by the heidelpayUI components (https://docs.heidelpay.com/docs/heidelpay-ui-components)
     *
     * @param BasePaymentType $paymentType The PaymentType object representing the object to be created.
     *
     * @return BasePaymentType|AbstractHeidelpayResource The created and updated PaymentType object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function createPaymentType(BasePaymentType $paymentType): BasePaymentType
    {
        return $this->resourceService->createPaymentType($paymentType);
    }

    /**
     * Retrieves a the PaymentType object with the given Id from the API.
     *
     * @param string $typeId The Id of the PaymentType resource to be fetched.
     *
     * @return BasePaymentType|AbstractHeidelpayResource The fetched PaymentType object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchPaymentType($typeId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchPaymentType($typeId);
    }

    //</editor-fold>

    //<editor-fold desc="Customer resource">

    /**
     * Creates a Customer resource via API using the given Customer object.
     *
     * @param Customer $customer The Customer object to be created using the API.
     *
     * @return Customer The created and updated Customer object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function createCustomer(Customer $customer): AbstractHeidelpayResource
    {
        return $this->resourceService->createCustomer($customer);
    }

    /**
     * Creates a Customer resource via API using the given Customer object.
     *
     * @param Customer $customer The Customer object to be created using the API.
     *
     * @return Customer The created and updated Customer object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function createOrUpdateCustomer(Customer $customer): AbstractHeidelpayResource
    {
        return $this->resourceService->createOrUpdateCustomer($customer);
    }

    /**
     * Updates the given local Customer object using the API.
     * Retrieves a Customer resource, if the customer parameter is the customer id.
     *
     * @param Customer|string $customer Either the local Customer object to be updated or the id of a Customer object
     *                                  to be retrieved from the API.
     *
     * @return Customer The retrieved/updated Customer object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchCustomer($customer): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchCustomer($customer);
    }

    /**
     * Retrieves a Customer resource, by the given external customer id.
     *
     * @param string $customerId The external customer id to fetch the customer object by.
     *
     * @return Customer The retrieved Customer object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchCustomerByExtCustomerId($customerId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchCustomerByExtCustomerId($customerId);
    }

    /**
     * Updates the remote Customer resource using the changes of the given local Customer object.
     *
     * @param Customer $customer The local Customer object used to update the remote resource via API.
     *
     * @return Customer The updated Customer object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function updateCustomer(Customer $customer): AbstractHeidelpayResource
    {
        return $this->resourceService->updateCustomer($customer);
    }

    /**
     * Deletes the given Customer resource via API.
     * The $customer parameter can be either a Customer instance or the id of the Customer to be deleted.
     *
     * @param Customer|string $customer Either the Customer object or the id of the Customer resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function deleteCustomer($customer)
    {
        $this->resourceService->deleteCustomer($customer);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization resource">

    /**
     * Retrieves an Authorization resource via the API using the corresponding Payment.
     * The Authorization resource can not be fetched using its id since they are unique only within the Payment.
     * A Payment can have zero or one Authorizations.
     *
     * @param Payment|string $payment The Payment object or the id of a Payment object whose Authorization to fetch.
     *
     * @return Authorization The Authorization object of the given Payment.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchAuthorization($payment): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchAuthorization($payment);
    }

    //</editor-fold>

    //<editor-fold desc="Charge resource">

    /**
     * Retrieve a Charge object by payment id and charge id from the API.
     * The Charge resource can not be fetched using its id since they are unique only within the Payment.
     *
     * @param string $paymentId The id of the Payment resource the Charge belongs to.
     * @param string $chargeId  The id of the Charge resource to be fetched.
     *
     * @return Charge The retrieved Charge object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchChargeById($paymentId, $chargeId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchChargeById($paymentId, $chargeId);
    }

    /**
     * Fetch the given Charge resource from the api.
     *
     * @param Charge $charge
     *
     * @return Charge
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchCharge(Charge $charge): AbstractHeidelpayResource
    {
        return $this->resourceService->fetch($charge);
    }

    //</editor-fold>

    //<editor-fold desc="Cancellation resource">

    /**
     * Retrieves a Cancellation resource of the given Authorization (aka reversal) via the API.
     *
     * @param Authorization $authorization  The Authorization object the Cancellation belongs to.
     * @param string        $cancellationId The id of the Cancellation object to be retrieved.
     *
     * @return Cancellation The retrieved Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchReversalByAuthorization($authorization, $cancellationId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchReversalByAuthorization($authorization, $cancellationId);
    }

    /**
     * Retrieves a Cancellation resource of the Authorization (aka reversal) which belongs to the Payment via API.
     *
     * @param Payment|string $payment        The Payment object or the id of the Payment the Reversal belongs to.
     * @param string         $cancellationId The id of the Authorization Cancellation (aka reversal).
     *
     * @return Cancellation The cancellation object retrieved from the API.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchReversal($payment, $cancellationId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchReversal($payment, $cancellationId);
    }

    /**
     * Retrieves the Cancellation object of a Charge (aka refund) from the API.
     *
     * @param Payment|string $payment        The Payment object or the id of the Payment the Cancellation belongs to.
     * @param string         $chargeId       The id of the Charge the Cancellation belongs to.
     * @param string         $cancellationId The id of the Cancellation resource.
     *
     * @return Cancellation The retrieved Cancellation resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchRefundById($payment, $chargeId, $cancellationId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchRefundById($payment, $chargeId, $cancellationId);
    }

    /**
     * Retrieves and fetches a Cancellation resource of a Charge (aka refund) via API.
     *
     * @param Charge $charge         The Charge object the Cancellation belongs to.
     * @param string $cancellationId The id of the Cancellation object to be retrieved.
     *
     * @return Cancellation The retrieved Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchRefund(Charge $charge, $cancellationId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetch($charge->getCancellation($cancellationId, true));
    }

    //</editor-fold>

    //<editor-fold desc="Shipment resource">

    /**
     * Retrieves the Shipment resource of the given Payment resource by its id.
     *
     * @param Payment|string $payment    The Payment object or the id of the Payment the Shipment resource belongs to.
     * @param string         $shipmentId The id of the Shipment resource to be retrieved.
     *
     * @return Shipment The retrieved Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchShipment($payment, $shipmentId): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchShipment($payment, $shipmentId);
    }

    //</editor-fold>

    //<editor-fold desc="Payout resource">

    /**
     * Retrieves an Payout resource via the API using the corresponding Payment.
     * The Payout resource can not be fetched using its id since they are unique only within the Payment.
     * A Payment can have zero or one Payouts.
     *
     * @param Payment|string $payment The Payment object or the id of a Payment object whose Payout to fetch.
     *
     * @return Payout The Payout object of the given Payment.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function fetchPayout($payment): AbstractHeidelpayResource
    {
        return $this->resourceService->fetchPayout($payment);
    }

    //</editor-fold>

    //<editor-fold desc="Webhook resource">

    /**
     * Creates Webhook resource.
     *
     * @param string $url   The url the registered webhook event should be send to.
     * @param string $event The event to be registered.
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function createWebhook(string $url, string $event): Webhook
    {
        return $this->webhookService->createWebhook($url, $event);
    }

    /**
     * Updates the given local Webhook object using the API.
     * Retrieves a Webhook resource, if the webhook parameter is the webhook id.
     *
     * @param Webhook|string $webhook
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchWebhook($webhook): Webhook
    {
        return $this->webhookService->fetchWebhook($webhook);
    }

    /**
     * Updates the Webhook resource of the api with the given object.
     *
     * @param Webhook $webhook
     *
     * @return Webhook
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function updateWebhook($webhook): Webhook
    {
        return $this->webhookService->updateWebhook($webhook);
    }

    /**
     * Updates the given Webhook resource of the api with the given object.
     *
     * @param Webhook|string $webhook
     *
     * @return AbstractHeidelpayResource|Webhook|null
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function deleteWebhook($webhook)
    {
        return $this->webhookService->deleteWebhook($webhook);
    }

    /**
     * Retrieves all registered webhooks and returns them in an array.
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchAllWebhooks(): array
    {
        return $this->webhookService->fetchWebhooks();
    }

    /**
     * Deletes all registered webhooks.
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function deleteAllWebhooks()
    {
        $this->webhookService->deleteWebhooks();
    }

    /**
     * Registers multiple Webhook events at once.
     *
     * @param string $url    The url the registered webhook events should be send to.
     * @param array  $events The events to be registered.
     *
     * @return array
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function registerMultipleWebhooks(string $url, array $events): array
    {
        return $this->webhookService->createWebhooks($url, $events);
    }

    /**
     * Fetches a resource object based on the given event data.
     *
     * @param string|null $eventJson
     *
     * @return AbstractHeidelpayResource
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fetchResourceFromEvent($eventJson = null): AbstractHeidelpayResource
    {
        return $this->webhookService->fetchResourceByWebhookEvent($eventJson);
    }

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Transactions">

    //<editor-fold desc="Authorize transactions">

    /**
     * Performs an Authorization transaction and returns the resulting Authorization resource.
     *
     * @param float                  $amount           The amount to authorize.
     * @param string                 $currency         The currency of the amount.
     * @param string|BasePaymentType $paymentType      The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl        The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer         The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId          A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata         The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket           The Basket object corresponding to the payment.
     *                                                 The Basket object will be created automatically if it does not exist
     *                                                 yet (i.e. has no id).
     * @param bool|null              $card3ds          Enables 3ds channel for credit cards if available. This parameter is
     *                                                 optional and will be ignored if not applicable.
     * @param string|null            $invoiceId        The external id of the invoice.
     * @param string|null            $paymentReference A reference text for the payment.
     *
     * @return Authorization The resulting object of the Authorization resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
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
        $paymentReference = null
    ): AbstractTransactionType {
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
            $paymentReference
        );
    }

    /**
     * Performs an Authorization transaction using a Payment object and returns the resulting Authorization resource.
     *
     * @param float                $amount    The amount to authorize.
     * @param string               $currency  The currency of the amount.
     * @param Payment              $payment   The Payment object to create the Authorization for.
     * @param string               $returnUrl The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null $customer  The Customer object or the id of the customer resource to reference.
     * @param string|null          $orderId   A custom order id which can be set by the merchant.
     * @param Metadata|null        $metadata  The Metadata object containing custom information for the payment.
     * @param Basket|null          $basket    The Basket object corresponding to the payment.
     *                                        The Basket object will be created automatically if it does not exist
     *                                        yet (i.e. has no id).
     *
     * @return Authorization The resulting object of the Authorization resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function authorizeWithPayment(
        $amount,
        $currency,
        Payment $payment,
        $returnUrl = null,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null
    ): AbstractTransactionType {
        return $this->paymentService
            ->authorizeWithPayment($amount, $currency, $payment, $returnUrl, $customer, $orderId, $metadata, $basket);
    }

    //</editor-fold>

    //<editor-fold desc="Charge transactions">

    /**
     * Performs a Charge transaction and returns the resulting Charge resource.
     *
     * @param float                  $amount           The amount to charge.
     * @param string                 $currency         The currency of the amount.
     * @param string|BasePaymentType $paymentType      The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl        The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer         The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId          A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata         The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket           The Basket object corresponding to the payment.
     *                                                 The Basket object will be created automatically if it does not exist
     *                                                 yet (i.e. has no id).
     * @param bool|null              $card3ds          Enables 3ds channel for credit cards if available. This parameter is
     *                                                 optional and will be ignored if not applicable.
     * @param string|null            $invoiceId        The external id of the invoice.
     * @param string|null            $paymentReference A reference text for the payment.
     *
     * @return Charge The resulting object of the Charge resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
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
    ): AbstractTransactionType {
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
     * Performs a Charge transaction for the Authorization of the given Payment object.
     * To perform a full charge of the authorized amount leave the amount null.
     *
     * @param string|Payment $payment The Payment object the Authorization to charge belongs to.
     * @param float|null     $amount  The amount to charge.
     *
     * @return Charge The resulting object of the Charge resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function chargeAuthorization($payment, $amount = null): AbstractTransactionType
    {
        return $this->paymentService->chargeAuthorization($payment, $amount);
    }

    /**
     * Performs a Charge transaction for a specific Payment and returns the resulting Charge object.
     *
     * @param Payment|string $payment  The Payment object to be charged.
     * @param null           $amount   The amount to charge.
     * @param null           $currency The Currency of the charged amount.
     *
     * @return Charge The resulting Charge object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function chargePayment($payment, $amount = null, $currency = null): AbstractTransactionType
    {
        $paymentObject = $this->resourceService->getPaymentResource($payment);
        return $this->paymentService->chargePayment($paymentObject, $amount, $currency);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization Cancel/Reversal">

    /**
     * Performs a Cancellation transaction and returns the resulting Cancellation object.
     * Performs a full cancel if the parameter amount is null.
     *
     * @param Authorization $authorization The Authorization to be canceled.
     * @param float|null    $amount        The amount to be canceled.
     *
     * @return Cancellation The resulting Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancelAuthorization(Authorization $authorization, $amount = null): AbstractTransactionType
    {
        return $this->paymentService->cancelAuthorization($authorization, $amount);
    }

    /**
     * Performs a Cancellation transaction for the Authorization of the given Payment object.
     * Performs a full cancel if the parameter amount is null.
     *
     * @param Payment|string $payment The Payment object or the id of the Payment the Authorization belongs to.
     * @param float|null     $amount  The amount to be canceled.
     *
     * @return Cancellation Resulting Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancelAuthorizationByPayment($payment, $amount = null): AbstractTransactionType
    {
        return $this->paymentService->cancelAuthorizationByPayment($payment, $amount);
    }

    //</editor-fold>

    //<editor-fold desc="Charge Cancel/Refund">

    /**
     * Performs a Cancellation transaction for the given Charge and returns the resulting Cancellation object.
     * Performs a full cancel if the parameter amount is null.
     *
     * @param Payment|string $paymentId The Payment object or the id of the Payment the charge belongs to.
     * @param string         $chargeId  The id of the Charge to be canceled.
     * @param float|null     $amount    The amount to be canceled.
     *
     * @return Cancellation The resulting Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancelChargeById($paymentId, $chargeId, $amount = null): AbstractTransactionType
    {
        return $this->paymentService->cancelChargeById($paymentId, $chargeId, $amount);
    }

    /**
     * Performs a Cancellation transaction and returns the resulting Cancellation object.
     * Performs a full cancel if the parameter amount is null.
     *
     * @param Charge      $charge           The Charge object to create the Cancellation for.
     * @param float|null  $amount           The amount to be canceled.
     * @param string|null $reasonCode
     * @param string|null $paymentReference A reference string for the payment.
     *
     * @return Cancellation The resulting Cancellation object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function cancelCharge(
        Charge $charge,
        $amount = null,
        string $reasonCode = null,
        string $paymentReference = null
    ): AbstractTransactionType {
        return $this->paymentService->cancelCharge($charge, $amount, $reasonCode, $paymentReference);
    }

    //</editor-fold>

    //<editor-fold desc="Shipment transactions">

    /**
     * Performs a Shipment transaction and returns the resulting Shipment object.
     *
     * @param Payment|string $payment   The Payment object the the id of the Payment to ship.
     * @param string|null    $invoiceId The id of the invoice in the shop.
     * @param string|null    $orderId   The id of the order in shop.
     *
     * @return Shipment The resulting Shipment object.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     */
    public function ship($payment, $invoiceId = null, $orderId = null): AbstractHeidelpayResource
    {
        return $this->paymentService->ship($payment, $invoiceId, $orderId);
    }

    //</editor-fold>

    //<editor-fold desc="Payout transactions">

    /**
     * Performs a Payout transaction and returns the resulting Payout resource.
     *
     * @param float                  $amount           The amount to charge.
     * @param string                 $currency         The currency of the amount.
     * @param string|BasePaymentType $paymentType      The PaymentType object or the id of the PaymentType to use.
     * @param string                 $returnUrl        The URL used to return to the shop if the process requires leaving it.
     * @param Customer|string|null   $customer         The Customer object or the id of the customer resource to reference.
     * @param string|null            $orderId          A custom order id which can be set by the merchant.
     * @param Metadata|null          $metadata         The Metadata object containing custom information for the payment.
     * @param Basket|null            $basket           The Basket object corresponding to the payment.
     *                                                 The Basket object will be created automatically if it does not exist
     *                                                 yet (i.e. has no id).
     * @param string|null            $invoiceId        The external id of the invoice.
     * @param string|null            $paymentReference A reference text for the payment.
     *
     * @return Payout The resulting object of the Payout resource.
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
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
        $paymentReference = null
    ): AbstractTransactionType {
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
            $paymentReference
        );
    }

    //</editor-fold>
    //</editor-fold>

    /**
     * @param Paypage       $paypage
     * @param Customer|null $customer
     * @param Basket|null   $basket
     * @param Metadata|null $metadata
     *
     * @return Paypage
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function initPayPageCharge(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->paymentService->initPayPage($paypage, TransactionTypes::CHARGE, $customer, $basket, $metadata);
    }

    /**
     * @param Paypage       $paypage
     * @param Customer|null $customer
     * @param Basket|null   $basket
     * @param Metadata|null $metadata
     *
     * @return Paypage
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function initPayPageAuthorize(
        Paypage $paypage,
        Customer $customer = null,
        Basket $basket = null,
        Metadata $metadata = null
    ): Paypage {
        return $this->paymentService->initPayPage(
            $paypage,
            TransactionTypes::AUTHORIZATION,
            $customer,
            $basket,
            $metadata
        );
    }

    //<editor-fold desc="Helpers">

    /**
     * Writes the given string to the registered debug handler if debug mode is enabled.
     *
     * @param $message
     */
    public function debugLog($message)
    {
        if ($this->isDebugMode()) {
            $debugHandler = $this->getDebugHandler();
            if ($debugHandler instanceof DebugHandlerInterface) {
                $debugHandler->log($message);
            }
        }
    }

    //</editor-fold>
}
