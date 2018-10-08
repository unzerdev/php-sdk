<?php
/**
 * This is the base class for all transaction types.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
