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
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;

class Heidelpay implements HeidelpayParentInterface
{
    const URL_TEST = 'https://dev-api.heidelpay.com/';
    const URL_LIVE = 'https://api.heidelpay.com/';
    const API_VERSION = 'v1';

    /** @var string $key */
    private $key;

    /** @var string $locale */
    private $locale;

    /** @var Payment $payment */
    private $payment;

    /** @var Customer $customer */
    private $customer;

    /** @var bool */
    private $sandboxMode = true;

    /** @var HttpAdapterInterface $adapter */
    private $adapter;

    /** @var PaymentTypeInterface $paymentType */
    private $paymentType;

    /**
     * @param string $key
     * @param string $locale
     * @param string $mode
     */
    public function __construct($key, $locale = SupportedLocale::GERMAN_GERMAN, $mode = Mode::TEST)
    {
        $this->key = $key;
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
     * @return Payment
     */
    public function getPayment(): Payment
    {
        if ($this->payment instanceof Payment) {
            return $this->payment;
        }

        if (empty($this->paymentId)) {
            throw new MissingResourceException('Payment object does not exist.');
        }

        // todo: fetch payment from api and return it

        return $this->payment;
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

    //<editor-fold desc="Resources">
    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        $this->customer->fetch();
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
     * @return PaymentTypeInterface
     */
    public function getPaymentType(): PaymentTypeInterface
    {
        if (!$this->paymentType instanceof PaymentTypeInterface) {
            throw new MissingResourceException();
        }

        return $this->paymentType;
    }

    //</editor-fold>

    /**
     * Set the given payment type and create it via api.
     *
     * @param PaymentTypeInterface $paymentType
     * @return PaymentTypeInterface
     */
    public function createPaymentType(PaymentTypeInterface $paymentType): PaymentTypeInterface
    {
        /** @var AbstractHeidelpayResource $paymentType */
        $paymentType->setParentResource($this);

        $this->paymentType = $paymentType;

        /** @var HeidelpayResourceInterface $paymentType */
        $type = $paymentType->create();

        /** @var PaymentTypeInterface $type */
        return $type;
    }
}
