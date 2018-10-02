<?php
/**
 * This is the heidelpay object which is the base object providing all functionalities needed to
 * access the api.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk
 */
namespace heidelpay\MgwPhpSdk;

use heidelpay\MgwPhpSdk\Adapter\CurlAdapter;
use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\Mode;
use heidelpay\MgwPhpSdk\Constants\SupportedLocale;
use heidelpay\MgwPhpSdk\Constants\TransactionTypes;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\MgwPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\GiroPay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\MgwPhpSdk\Services\ResourceService;

class Heidelpay implements HeidelpayParentInterface
{
    const URL_TEST = 'https://dev-api.heidelpay.com/';
    const URL_LIVE = 'https://api.heidelpay.com/';
    const API_VERSION = 'v1';
    const SDK_VERSION = 'HeidelpayPHP 1.0.0-beta';
    const DEBUG_MODE = true;

    /**
     * @param string $key
     * @param string $locale
     * @param string $mode
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN, $mode = Mode::TEST)
    {
        $this->setKey($key);
        $this->setMode($mode);
        $this->locale = $locale;

        $this->resourceService = new ResourceService();
    }

    /**
     * @param $uri
     * @param HeidelpayResourceInterface $resource
     * @param string $method
     * @return string
     */
    public function send(
        $uri,
        HeidelpayResourceInterface $resource,
        $method = HttpAdapterInterface::REQUEST_GET
    ): string
    {
        if (!$this->adapter instanceof HttpAdapterInterface) {
            $this->adapter = new CurlAdapter();
        }
        $url = $this->isSandboxMode() ? self::URL_TEST : self::URL_LIVE;
        return $this->adapter->send($url . self::API_VERSION . $uri, $resource, $method);
    }

    //<editor-fold desc="Properties">
    /** @var string $key */
    private $key;

    /** @var string $locale */
    private $locale;

    /** @var bool */
    private $sandboxMode = true;

    /** @var HttpAdapterInterface $adapter */
    private $adapter;

    /** @var ResourceService $resourceService */
    private $resourceService;

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Heidelpay
     */
    public function setKey($key): Heidelpay
    {
        $isPrivateKey = strpos($key, 's-priv-') !== false;
        if (!$isPrivateKey) {
            throw new IllegalKeyException();
        }

        $this->key = $key;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     * @return Heidelpay
     */
    public function setSandboxMode($sandboxMode): Heidelpay
    {
        $this->sandboxMode = $sandboxMode;
        return $this;
    }

    /**
     * @param $mode
     * @return Heidelpay
     */
    private function setMode($mode): Heidelpay
    {
        if ($mode !== Mode::TEST) {
            $this->setSandboxMode(false);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Heidelpay
     */
    public function setLocale($locale): Heidelpay
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return ResourceService
     */
    public function getResourceService(): ResourceService
    {
        return $this->resourceService;
    }

    /**
     * @param ResourceService $resourceService
     * @return Heidelpay
     */
    public function setResourceService(ResourceService $resourceService): Heidelpay
    {
        $this->resourceService = $resourceService;
        return $this;
    }

    //<editor-fold desc="ParentIF">
    /**
     * Returns the heidelpay root object.
     *
     * @return Heidelpay
     */
    public function getHeidelpayObject(): Heidelpay
    {
        return $this;
    }

    /**
     * Returns the url string for this resource.
     *
     * @return string
     */
    public function getUri(): string
    {
        return '';
    }
    //</editor-fold>

    //</editor-fold>

    //</editor-fold>

    //<editor-fold desc="Resources">
    //<editor-fold desc="Payment resource">
    /**
     * @param PaymentTypeInterface $paymentType
     * @return Payment
     */
    private function createPayment(PaymentTypeInterface $paymentType): Payment
    {
        $payment = new Payment($this);
        $payment->setPaymentType($paymentType);
        return $payment;
    }

    /**
     * Fetch and return payment by given payment id.
     *
     * @param $paymentId
     * @return HeidelpayResourceInterface
     */
    public function fetchPaymentById($paymentId): HeidelpayResourceInterface
    {
        $payment = new Payment($this);
        $payment->setId($paymentId);
        return $this->resourceService->fetch($payment);
    }
    //</editor-fold>

    //<editor-fold desc="PaymentType resource">
    /**
     * Create the given payment type via api.
     *
     * @param PaymentTypeInterface $paymentType
     * @return PaymentTypeInterface|AbstractHeidelpayResource
     */
    public function createPaymentType(PaymentTypeInterface $paymentType)
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this);
        return $this->resourceService->create($paymentType);
    }

    /**
     * @param string $typeId
     * @return mixed
     */
    public function fetchPaymentType($typeId)
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3})/', $typeId, $typeIdParts);

        // todo maybe move this into a builder service
        switch ($typeIdParts[1]) {
            case 'crd':
                $paymentType = new Card(null, null);
                break;
            case 'gro':
                $paymentType = new GiroPay();
                break;
            case 'idl':
                $paymentType = new Ideal();
                break;
            case 'ivc':
                $paymentType = new Invoice();
                break;
            default:
                throw new IllegalTransactionTypeException($typeId);
                break;
        }

        return $this->resourceService->fetch($paymentType->setParentResource($this)->setId($typeId));
    }
    //</editor-fold>

    //<editor-fold desc="Customer resource">
    /**
     * Create the given Customer object via API.
     *
     * @param Customer $customer
     * @return mixed
     */
    public function createCustomer(Customer $customer): Customer
    {
        $customer->setParentResource($this);
        return $this->resourceService->create($customer);
    }

    /**
     * Fetch and return Customer object from API by the given id.
     *
     * @param $customerId
     * @return HeidelpayResourceInterface
     */
    public function fetchCustomerById($customerId): HeidelpayResourceInterface
    {
        $customer = (new Customer())->setParentResource($this)->setId($customerId);
        return $this->resourceService->fetch($customer);
    }

    /**
     * Update and return a Customer object via API.
     *
     * @param Customer $customer
     * @return HeidelpayResourceInterface
     */
    public function updateCustomer(Customer $customer): HeidelpayResourceInterface
    {
        return $this->resourceService->update($customer);
    }

    /**
     * Delete a Customer object via API.
     * @param $customerId
     * @throws HeidelpayApiException
     */
    public function deleteCustomerById($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->fetchCustomerById($customerId);
        $this->deleteCustomer($customer);
    }

    /**
     * @param Customer $customer
     * @throws HeidelpayApiException
     */
    public function deleteCustomer(Customer $customer)
    {
        $this->getResourceService()->delete($customer);
    }
    //</editor-fold>

    //<editor-fold desc="Authorization resource">
    /**
     * @param $paymentId
     * @return Authorization|null
     */
    public function fetchAuthorization($paymentId)
    {
        /** @var Payment $payment */
        $payment = $this->fetchPaymentById($paymentId);
        return $payment->getAuthorization();
    }
    //</editor-fold>
    //</editor-fold>

    //<editor-fold desc="Transactions">
    //<editor-fold desc="Authorize transactions">
    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float $amount
     * @param string $currency
     * @param $paymentTypeId
     * @param string $returnUrl
     * @param Customer|null $customer
     * @return Authorization
     */
    public function authorize($amount, $currency, $paymentTypeId, $returnUrl, $customer = null): Authorization
    {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->authorizeWithPaymentType($amount, $currency, $paymentType, $returnUrl, $customer);
    }

    /**
     * Perform authorization with the given payment type id.
     *
     * @param $amount
     * @param $currency
     * @param $paymentTypeId
     * @param $returnUrl
     * @param null $customer
     * @return mixed
     */
    public function authorizeWithPaymentTypeId(
        $amount,
        $currency,
        $paymentTypeId,
        $returnUrl,
        $customer = null
    ): Authorization
    {
        $method = TransactionTypes::AUTHORIZATION;
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        if (\is_callable([$paymentType, $method])) {
            return $paymentType->$method($amount, $currency, $returnUrl, $customer);
        }

        throw new IllegalTransactionTypeException(TransactionTypes::AUTHORIZATION);
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float $amount
     * @param string $currency
     * @param $paymentType
     * @param string $returnUrl
     * @param Customer|null $customer
     * @return Authorization
     */
    public function authorizeWithPaymentType($amount, $currency, $paymentType, $returnUrl, $customer = null): Authorization
    {
        $payment = $this->createPayment($paymentType);

        if ($customer instanceof Customer) {
            if ($customer->getId() === null) {
                $this->createCustomer($customer);
            }
            $payment->setCustomer($customer);
        }

        $authorization = new Authorization($amount, $currency, $returnUrl);
        $payment->setAuthorization($authorization);
        $this->resourceService->create($authorization);
        return $authorization;
    }
    //</editor-fold>

    //<editor-fold desc="Charge transactions">
    /**
     * Performs a charge and returns the corresponding Charge object.
     *
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @param string $paymentTypeId
     * @param Customer|null $customer
     * @return Charge
     */
    public function charge($amount, $currency, $paymentTypeId, $returnUrl, $customer = null): Charge
    {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->chargeWithPaymentType($amount, $currency, $paymentType, $returnUrl, $customer);
    }

    /**
     * @param $paymentId
     * @param null $amount
     * @return Charge
     */
    public function chargeAuthorization($paymentId, $amount = null): Charge
    {
        /** @var Payment $payment */
        $payment = $this->fetchPaymentById($paymentId);
        $charge = new Charge($amount);
        $charge->setParentResource($payment)->setPayment($payment);
        $this->resourceService->create($charge);
        // needs to be set after creation to use id as key in charge array
        $payment->addCharge($charge);

        return $charge;
    }

    /**
     * @param PaymentTypeInterface $paymentType
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param null $customer
     * @return Charge
     */
    public function chargeWithPaymentType(
        $amount,
        $currency,
        PaymentTypeInterface $paymentType,
        $returnUrl,
        $customer = null): Charge
    {
        $payment = $this->createPayment($paymentType);

        if ($customer instanceof Customer) {
            $payment->setCustomer($customer);
        }

        /** @var Charge $charge */
        $charge = new Charge($amount, $currency, $returnUrl);
        $charge->setParentResource($payment)->setPayment($payment);
        $this->resourceService->create($charge);
        // needs to be set after creation to use id as key in charge array
        $payment->addCharge($charge);

        return $charge;
    }
    //</editor-fold>
    //</editor-fold>
}
