<?php
/**
 * This is the base class for all transaction types.
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
 * @package  heidelpay/mgw_sdk/transaction_types
 */
namespace heidelpay\MgwPhpSdk\Resources\TransactionTypes;

use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayResourceInterface;

abstract class AbstractTransactionType extends AbstractHeidelpayResource
{
    /** @var Payment $payment */
    private $payment;

    //<editor-fold desc="Getters/Setters">
    /**
     * @return Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment($payment): self
    {
        $this->payment = $payment;
        return $this;
    }
    //</editor-fold>

    public function handleResponse(\stdClass $response)
    {
        parent::handleResponse($response);
        $this->updatePayment();
    }

    /**
     * Updates the payment object if it exists and if this is not the payment object.
     * This is called from the crud methods to update the payments state whenever anything happens.
     */
    private function updatePayment()
    {
        if (!$this instanceof Payment) {
            $payment = $this->getPayment();
            if ($payment instanceof HeidelpayResourceInterface) {
                $this->getHeidelpayObject()->getResourceService()->fetch($payment);
            }
        }
    }
}
