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

namespace heidelpay\NmgPhpSdk\TransactionTypes;

use heidelpay\NmgPhpSdk\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;

class Authorization extends AbstractHeidelpayResource
{
    /** @var float $amount */
    protected $amount = 0.0;

    /** @var string $currency */
    protected $currency = '';

    /** @var string $returnUrl */
    protected $returnUrl = '';

    /** @var string $uniqueId */
    protected $uniqueId = '';

    /**
     * Authorization constructor.
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     */
    public function __construct($amount, $currency, $returnUrl)
    {
        $this->setAmount($amount);
        $this->setCurrency($currency);
        $this->setReturnUrl($returnUrl);
    }

    //<editor-fold desc="Setters/Getters">
    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Authorization
     */
    public function setAmount(float $amount): Authorization
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Authorization
     */
    public function setCurrency(string $currency): Authorization
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     * @return Authorization
     */
    public function setReturnUrl(string $returnUrl): Authorization
    {
        $this->returnUrl = $returnUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     * @return Authorization
     */
    public function setUniqueId(string $uniqueId): Authorization
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }
    //</editor-fold>

    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'authorize';
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkedResources(): array
    {
        /** @var Heidelpay $heidelpay */
        $heidelpay = $this->getHeidelpayObject();
        $paymentType = $heidelpay->getPaymentType();
        if (!$paymentType instanceof PaymentTypeInterface) {
            throw new MissingResourceException();
        }

        return [
            'customer'=> $heidelpay->getCustomer(),
            'type' => $heidelpay->getPaymentType()
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function handleResponse(\stdClass $response)
    {
        if (!isset($response->resources->paymentId)) {
            return;
        }

        $payment = $this->getHeidelpayObject()->getOrCreatePayment();
        $payment->setId($response->resources->paymentId);
    }
}
