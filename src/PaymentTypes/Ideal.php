<?php
/**
 * This represents the ideal payment type.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/PaymentTypes
 */
namespace heidelpay\NmgPhpSdk\PaymentTypes;

class Ideal extends BasePaymentType
{
    /** @var string $bankName */
    protected $bankName;

    /**
     * GiroPay constructor.
     */
    public function __construct()
    {
        $this->setChargeable(true);

        parent::__construct();
    }

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/ideal';
    }
    //</editor-fold>

    //<editor-fold desc="Getter/Setter">
    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     * @return Ideal
     */
    public function setBankName(string $bankName): Ideal
    {
        $this->bankName = $bankName;
        return $this;
    }
    //</editor-fold>
}
