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

use heidelpay\NmgPhpSdk\Adapter\CurlAdapter;
use heidelpay\NmgPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\NmgPhpSdk\Constants\Mode;
use heidelpay\NmgPhpSdk\Constants\SupportedLocale;
use heidelpay\NmgPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

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

    /**
     * @param string $key
     * @param string $locale
     * @param string $mode
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN, $mode = Mode::TEST)
    {
        $this->setKey($key);
        $this->locale = $locale;

        $this->setMode($mode);
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
     * @return PaymentTypeInterface
     */
    public function createPaymentType(PaymentTypeInterface $paymentType): PaymentTypeInterface
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this);
        return $paymentType->create();
    }

    /**
     * Create the given customer via api.
     *
     * @param Customer $customer
     * @return mixed
     */
    public function createCustomer(Customer $customer): Customer
    {
        /** @var AbstractHeidelpayResource $customer */
        $customer->setParentResource($this);
        return $customer->create();
    }

    /**
     * @param PaymentTypeInterface $paymentType
     * @return Payment
     */
    public function createPayment(PaymentTypeInterface $paymentType): Payment
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
        return $payment->fetch();
    }

    /**
     * @param $typeId
     * @return mixed
     */
    public function fetchPaymentType($typeId)
    {
        $paymentType = null;

        $typeIdParts = [];
        preg_match('/^[sp]{1}-([a-z]{3})/', $typeId,$typeIdParts);

        // todo maybe move this into a builder service
        switch ($typeIdParts[1]) {
            case 'crd':
                $paymentType = (new Card('', ''))->setParentResource($this)->setId($typeId)->fetch();
                break;
            default:
                throw new IllegalTransactionTypeException($typeId);
                break;
        }

        return $paymentType;
    }

    /**
     * Performs an authorization and returns the corresponding Authorization object.
     *
     * @param PaymentTypeInterface $paymentType
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Authorization
     */
    public function authorize(PaymentTypeInterface $paymentType, $amount, $currency, $returnUrl): Authorization
    {
        $payment = $this->createPayment($paymentType);
        return $payment->authorize($amount, $currency, $returnUrl);
    }

    /**
     * Performs a charge and returns the corresponding Charge object.
     *
     * @param PaymentTypeInterface $paymentType
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Charge
     */
    public function charge(PaymentTypeInterface $paymentType, $amount, $currency, $returnUrl): Charge
    {
        $payment = $this->createPayment($paymentType);
        return $payment->charge($amount, $currency, $returnUrl);
    }
}
