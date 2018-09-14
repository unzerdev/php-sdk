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
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

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
    public function charge($amount, $currency, $returnUrl): Charge
    {
        if ($this->isChargeable()) {
            return $this->getHeidelpayObject()->charge($this, $amount, $currency, $returnUrl);
        }

        throw new IllegalTransactionTypeException('charge');
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl): Authorization
    {
        if ($this->isAuthorizable()) {
            return $this->getHeidelpayObject()->authorize($this, $amount, $currency, $returnUrl);
        }

        throw new IllegalTransactionTypeException('authorize');
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
