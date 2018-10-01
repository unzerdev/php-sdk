<?php
/**
 * This represents the SEPA direct debit guaranteed payment type.
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
 * @package  heidelpay/ngw_sdk/payment_types
 */
namespace heidelpay\NmgPhpSdk\Resources\PaymentTypes;

class SepaDirectDebitGuaranteed extends BasePaymentType
{
    /** @var string $iban */
    protected $iban;

    /** @var string $bic */
    protected $bic;

    /** @var string $holder */
    protected $holder;

    /**
     * @param string $iban
     */
    public function __construct(string $iban)
    {
        $this->setChargeable(true);

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
     * @return SepaDirectDebitGuaranteed
     */
    public function setIban(string $iban): SepaDirectDebitGuaranteed
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
     * @return SepaDirectDebitGuaranteed
     */
    public function setBic(string $bic): SepaDirectDebitGuaranteed
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
     * @return SepaDirectDebitGuaranteed
     */
    public function setHolder(string $holder): SepaDirectDebitGuaranteed
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
