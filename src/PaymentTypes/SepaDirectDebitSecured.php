<?php
/**
 * This represents the SEPA direct debit secured payment type.
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

class SepaDirectDebitSecured extends BasePaymentType
{
    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /**
     * SepaDirectDebitSecured constructor.
     * @param string $iban
     */
    public function __construct(string $iban)
    {
        $this->iban = $iban;

        parent::__construct();
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getIban(): string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     * @return SepaDirectDebitSecured
     */
    public function setIban(string $iban): SepaDirectDebitSecured
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     * @return SepaDirectDebitSecured
     */
    public function setBic(string $bic): SepaDirectDebitSecured
    {
        $this->bic = $bic;
        return $this;
    }

    /**
     * @return string
     */
    public function getHolder(): string
    {
        return $this->holder;
    }

    /**
     * @param string $holder
     * @return SepaDirectDebitSecured
     */
    public function setHolder(string $holder): SepaDirectDebitSecured
    {
        $this->holder = $holder;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/sepa-direct-debit-guaranteed';
    }
    //</editor-fold>
}
