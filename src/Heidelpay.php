<?php
/**
 * Description
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
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk;

use heidelpay\NmgPhpSdk\Adapter\CurlAdapter;
use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Constants\Mode;
use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Resources\Customer;
use heidelpay\NmgPhpSdk\Resources\Payment;
use heidelpay\NmgPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\GiroPay;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\NmgPhpSdk\Interfaces\HeidelpayResourceInterface;
use heidelpay\NmgPhpSdk\Interfaces\PaymentTypeInterface;
use heidelpay\NmgPhpSdk\Service\ResourceService;

class Heidelpay implements HeidelpayParentInterface
{
    const URL_TEST = 'https://dev-api.heidelpay.com/';
    const URL_LIVE = 'https://api.heidelpay.com/';
    const API_VERSION = 'v1';

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
    //</editor-fold>

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
     * Create the given customer via api.
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

    /**
     * Fetch and return customer by given customer id.
     *
     * @param $customerId
     * @return HeidelpayResourceInterface
     */
    public function fetchCustomerById($customerId): HeidelpayResourceInterface
    {
        $customer = (new Customer())->setParentResource($this)->setId($customerId);
        return $this->resourceService->create($customer);
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
    
    //<editor-fold desc="Authorize methods">

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float $amount
     * @param string $currency
     * @param $paymentTypeId
     * @param string $returnUrl
     * @return Authorization
     */
    public function authorize($amount, $currency, $paymentTypeId, $returnUrl): Authorization
    {
        $paymentType = $this->fetchPaymentType($paymentTypeId);
        return $this->authorizeWithPaymentType($amount, $currency, $paymentType, $returnUrl);
    }

    /**
     * Perform an authorization and return the corresponding Authorization object.
     *
     * @param float $amount
     * @param string $currency
     * @param $paymentType
     * @param string $returnUrl
     * @return Authorization
     */
    public function authorizeWithPaymentType($amount, $currency, $paymentType, $returnUrl): Authorization
    {
        $payment = $this->createPayment($paymentType);
        $authorization = new Authorization($amount, $currency, $returnUrl);
        $payment->setAuthorization($authorization);
        $this->resourceService->create($authorization);
        return $authorization;
    }

    //</editor-fold>

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
}
