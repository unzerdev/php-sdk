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

use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Cancellation;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class Payment extends AbstractHeidelpayResource implements PaymentInterface
{
    /** @var string $redirectUrl */
    private $redirectUrl = '';

    /** @var Authorization $authorize */
    private $authorize;

    /** @var array $charges */
    private $charges = [];

    /** @var int */
    private $state;

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    const EPSILON = 0.000001;

    public function getResourcePath()
    {
        return 'payments';
    }
    //</editor-fold>

    //<editor-fold desc="Setters/Getters">
    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @param string $redirectUrl
     * @return Payment
     */
    public function setRedirectUrl(string $redirectUrl): Payment
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    /**
     * @return Authorization|null
     */
    public function getAuthorization()
    {
        return $this->authorize;
    }

    /**
     * @param Authorization $authorize
     * @return PaymentInterface
     */
    public function setAuthorization(Authorization $authorize): PaymentInterface
    {
        $this->authorize = $authorize;
        return $this;
    }

    /**
     * @return array
     */
    public function getCharges(): array
    {
        return $this->charges;
    }

    /**
     * @param array $charges
     * @return Payment
     */
    public function setCharges(array $charges): Payment
    {
        $this->charges = $charges;
        return $this;
    }

    /**
     * @param Charge $charge
     */
    public function addCharge(Charge $charge)
    {
        $this->charges[] = $charge;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     * @return Payment
     */
    public function setState(int $state): Payment
    {
        $this->state = $state;
        return $this;
    }
    //</editor-fold>

    //<editor-fold desc="TransactionTypes">

    /**
     * @return Charge
     */
    public function fullCharge(): Charge
    {
        // get remaining amount
        $remainingAmount = $this->getRemainingAmount();
        if ($remainingAmount === false) {
            throw new MissingResourceException('Cannot perform full charge without authorization.');
        }

        // charge amount
        return $this->charge($remainingAmount);
    }

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Charge
     */
    public function charge($amount = null, $currency = null, $returnUrl = null): Charge
    {
        if (!$this->getPaymentType()->isChargeable()) {
            throw new IllegalTransactionTypeException(__METHOD__);
        }

        if ($amount === null) {
            return $this->fullCharge();
        }

        $charge = new Charge($amount, $currency, $returnUrl);
        $this->addCharge($charge);
        $charge->setParentResource($this);
        $charge->create();
        return $charge;
    }

    /**
     * {@inheritDoc}
     */
    public function authorize($amount, $currency, $returnUrl): Authorization
    {
        if (!$this->getPaymentType()->isAuthorizable()) {
            throw new IllegalTransactionTypeException(__METHOD__);
        }

        $authorization = new Authorization($amount, $currency, $returnUrl);
        $this->setAuthorization($authorization);
        $authorization->setParentResource($this);
        $authorization->create();
        return $authorization;
    }

    /**
     * @return Cancellation
     */
    public function fullCancel(): Cancellation
    {
        if ($this->authorize instanceof Authorization) {
            $this->authorize->cancel();
        }


        // fullCancel on Auth w/o charges and cancels
        // PaymentState = cancelled --> https://heidelpay.atlassian.net/wiki/spaces/ID/pages/118030386/payments
        // cancel authorization

        // fullCancel on fully charged Auth
        // canceln der einzelnen charges

        // fullCancel on partly Charged Auth
        // canceln der einzelnen charges

        // fullCancel on partly Charged and partly canceled Auth
        // alle charges, die nicht gecancelled sind canceln

        // PartCancel on fully charged Auth
        // cancel auf den charge mit dem betrag
        // $payment->charge['key']->cancel(30) //todo bei arrays id als key

        // PartCancel on Auth w/o charges and cancels
        // $payment->auth->cancel(amount)

        // PartCancel on Auth w charges w/o cancels
        // fall-1: auth = 100, charge 60 , cancel = 40, state completed
        // fall-2: auth = 100, charged 60, cancel = 60, exception von der api

        // PartCancel on Auth o charges w cancels
        // s.o.

        // Auth = 100, cha: 60, auth.can = 40 = remaining=0; charge.cancel(60) -> auth.state = canceled

        // Speichere ich die cancels immer direkt in den charges?
        // muss immer genau ein charge gecancelled werden?

        // Berechnung in der api amounts nicht selber berechnen, sondern aus der api holen
        // nur payment updaten, wenn es benutzt wird



        // charge amount
//        return $this->cancel($remainingAmount);
    }

    /**
     * @param float $amount
     * @return Cancellation
     */
    public function cancel($amount = null): Cancellation
    {
        if (!$this->getPaymentType()->isCancelable()) {
            throw new IllegalTransactionTypeException(__METHOD__);
        }

        if (null === $amount) {
            return $this->fullCancel();
        }

        return new Cancellation($this);
    }
    //</editor-fold>

    /**
     * @return PaymentTypes\PaymentTypeInterface
     */
    private function getPaymentType(): PaymentTypes\PaymentTypeInterface
    {
        return $this->getHeidelpayObject()->getPaymentType();
    }

    /**
     * Calculate and return uncharged amount.
     * Returns false if authorization does not exist.
     *
     * @return float|bool
     */
    public function getRemainingAmount()
    {
        $authorization = $this->getAuthorization();
        if ($authorization === null) {
            return false;
        }

        $remainingAmount = $authorization->getAmount() - $this->getChargedAmount();

        if (abs($remainingAmount) < self::EPSILON) {
            $remainingAmount = 0.0;
        }
        return $remainingAmount;
    }

    /**
     * Return the amount already charged in this payment.
     *
     * @return float
     */
    public function getChargedAmount(): float
    {
        $chargedAmount = 0.0;

        /** @var Charge $charge */
        foreach ($this->getCharges() as $charge) {
            $chargedAmount += $charge->getAmount();
        }

        return $chargedAmount;
    }
}
