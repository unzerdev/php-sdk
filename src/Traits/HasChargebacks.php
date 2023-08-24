<?php
/**
 * This trait adds the cancellation property to a class.
 *
 * Copyright (C) 2023 - today Unzer E-Com GmbH
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
 * @package  UnzerSDK\Traits
 */

namespace UnzerSDK\Traits;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Interfaces\UnzerParentInterface;
use UnzerSDK\Resources\AbstractUnzerResource;
use RuntimeException;
use UnzerSDK\Resources\TransactionTypes\Chargeback;

trait HasChargebacks
{
    /** @var Chargeback[] $chargebacks */
    private $chargebacks = [];

    /**
     * @return array
     */
    public function getChargebacks(): array
    {
        return $this->chargebacks;
    }

    /**
     * @param array $chargebacks
     *
     * @return self
     */
    public function setChargebacks(array $chargebacks): self
    {
        $this->chargebacks = $chargebacks;
        return $this;
    }

    /**
     * @param Chargeback $chargeback
     *
     * @return self
     */
    public function addChargeback(Chargeback $chargeback): self
    {
        if ($this instanceof UnzerParentInterface) {
            $chargeback->setParentResource($this);
        }
        $this->chargebacks[] = $chargeback;
        return $this;
    }

    /**
     * Return specific Chargeback object or null if it does not exist.
     *
     * @param string  $chargebackId The id of the chargeback object
     * @param boolean $lazy
     *
     * @return Chargeback|null The chargeback or null if none could be found.
     *
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     */
    public function getChargeback(string $chargebackId, bool $lazy = false): ?Chargeback
    {
        /** @var Chargeback $chargeback */
        foreach ($this->chargebacks as $chargeback) {
            if ($chargeback->getId() === $chargebackId) {
                if (!$lazy && $this instanceof UnzerParentInterface) {
                    /** @var AbstractUnzerResource $this*/
                    $this->getResource($chargeback);
                }
                return $chargeback;
            }
        }
        return null;
    }
}
