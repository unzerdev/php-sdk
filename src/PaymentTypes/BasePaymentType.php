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
namespace heidelpay\NmgPhpSdk\PaymentTypes;

use heidelpay\NmgPhpSdk\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

abstract class BasePaymentType extends AbstractHeidelpayResource implements PaymentTypeInterface, PaymentInterface
{
    protected $chargeable = false;
    protected $authorizable = false;
    protected $cancelable = false;

    //<editor-fold desc="Getters">
    /**
     * @return bool
     */
    public function isChargeable(): bool
    {
        return $this->chargeable;
    }

    /**
     * @return bool
     */
    public function isAuthorizable(): bool
    {
        return $this->authorizable;
    }

    /**
     * @return bool
     */
    public function isCancelable(): bool
    {
        return $this->cancelable;
    }
    //</editor-fold>

    //<editor-fold desc="Transactions">
    /**
     * {@inheritDoc}
     */
    public function charge($amount = null, $currency = ''): Charge
    {
        $payment = $this->getHeidelpayObject()->getOrCreatePayment();
        return $payment->charge($amount, $currency);
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl): Authorization
    {
        $payment = $this->getHeidelpayObject()->getOrCreatePayment();
        return $payment->authorize($amount, $currency, $returnUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function cancel($amount = null)
    {
        $payment = $this->getHeidelpayObject()->getOrCreatePayment();
        return $payment->cancel($amount);
    }
    //</editor-fold>
}
