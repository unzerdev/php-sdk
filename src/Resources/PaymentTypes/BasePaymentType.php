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
namespace heidelpay\NmgPhpSdk\Resources\PaymentTypes;

use heidelpay\NmgPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\NmgPhpSdk\Interfaces\PaymentTypeInterface;

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

        return $this->getHeidelpayObject()->charge($this, $amount, $currency, $returnUrl, $customer);
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl): Authorization
    {
        if (!$this->isAuthorizable()) {
            throw new IllegalTransactionTypeException('authorize');
        }

        return $this->getHeidelpayObject()->authorize($this, $amount, $currency, $returnUrl);
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
