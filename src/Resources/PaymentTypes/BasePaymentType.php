<?php
/**
 * This defines a base class for all payment types e.g. Card, GiroPay, etc.
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
 * @package  heidelpay/mgw_sdk/payment_types
 */
namespace heidelpay\MgwPhpSdk\Resources\PaymentTypes;

use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Interfaces\PaymentTypeInterface;

abstract class BasePaymentType extends AbstractHeidelpayResource implements PaymentTypeInterface
{
    private $authorizable = false;
    private $chargeable = false;

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/' . $this::getClassShortName();
    }
    //</editor-fold>

    //<editor-fold desc="Transaction methods">
    /**
     * {@inheritDoc}
     */
    public function charge($amount, $currency, $returnUrl, $customer = null): Charge
    {
        if (!$this->isChargeable()) {
            throw new IllegalTransactionTypeException('charge');
        }

        return $this->getHeidelpayObject()->chargeWithPaymentType($amount, $currency, $this, $returnUrl, $customer);
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl, $customer = null): Authorization
    {
        if (!$this->isAuthorizable()) {
            throw new IllegalTransactionTypeException('authorize');
        }

        return $this->getHeidelpayObject()->authorizeWithPaymentType($amount, $currency, $this, $returnUrl, $customer);
    }

    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * @return bool
     */
    public function isAuthorizable(): bool
    {
        return $this->authorizable;
    }

    /**
     * @param bool $authorizable
     * @return BasePaymentType
     */
    public function setAuthorizable(bool $authorizable): BasePaymentType
    {
        $this->authorizable = $authorizable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChargeable(): bool
    {
        return $this->chargeable;
    }

    /**
     * @param bool $chargeable
     * @return BasePaymentType
     */
    public function setChargeable(bool $chargeable): BasePaymentType
    {
        $this->chargeable = $chargeable;
        return $this;
    }
    //</editor-fold>
}
