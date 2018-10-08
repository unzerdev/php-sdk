<?php
/**
 * This represents the cancel transaction.
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

class Cancellation extends AbstractTransactionType
{
    /** @var float $amount */
    private $amount;

    /**
     * Authorization constructor.
     * @param float $amount
     */
    public function __construct($amount = null)
    {
        $this->setAmount($amount);

        parent::__construct(null);
    }

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath()
    {
        return 'cancels';
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Cancellation
     */
    public function setAmount($amount): Cancellation
    {
        $this->amount = $amount;
        return $this;
    }
    //</editor-fold>
}
