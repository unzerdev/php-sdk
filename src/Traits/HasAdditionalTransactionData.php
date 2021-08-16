<?php
/**
 * This trait allows a transaction type to have additional transaction Data.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.unzer.com/
 *
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\Traits
 */

namespace UnzerSDK\Traits;

use stdClass;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

trait HasAdditionalTransactionData
{

    /** @var stdClass $additionalTransactionData */
    protected $additionalTransactionData;

    /** Return additionalTransactionData as a Std class object.
     *
     * @return stdClass|null
     */
    public function getAdditionalTransactionData(): ?stdClass
    {
        return $this->additionalTransactionData;
    }

    /**
     * @param stdClass $additionalTransactionData
     *
     * @return AbstractTransactionType
     */
    public function setAdditionalTransactionData(stdClass $additionalTransactionData): self
    {
        $this->additionalTransactionData = $additionalTransactionData;
        return $this;
    }

    /** Add a single element to the additionalTransactionData object.
     *
     * @param mixed $value
     * @param mixed $name
     *
     * @return AbstractTransactionType
     */
    public function addAdditionalTransactionData($name, $value): self
    {
        if (null === $this->additionalTransactionData) {
            $this->additionalTransactionData = new stdClass();
        }
        $this->additionalTransactionData->$name = $value;
        return $this;
    }
}
