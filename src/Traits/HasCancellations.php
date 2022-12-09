<?php
/**
 * This trait adds the cancellation property to a class.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use RuntimeException;

trait HasCancellations
{
    /** @var array $cancellations */
    private $cancellations = [];

    //<editor-fold desc="Getters/Setters">

    /**
     * @return array
     */
    public function getCancellations(): array
    {
        return $this->cancellations;
    }

    /**
     * @param array $cancellations
     *
     * @return self
     */
    public function setCancellations(array $cancellations): self
    {
        $this->cancellations = $cancellations;
        return $this;
    }

    /**
     * @param Cancellation $cancellation
     *
     * @return self
     */
    public function addCancellation(Cancellation $cancellation): self
    {
        if ($this instanceof UnzerParentInterface) {
            $cancellation->setParentResource($this);
        }
        $this->cancellations[] = $cancellation;
        return $this;
    }

    /**
     * Return specific Cancellation object or null if it does not exist.
     *
     * @param string  $cancellationId The id of the cancellation object
     * @param boolean $lazy
     *
     * @return Cancellation|null The cancellation or null if none could be found.
     *
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     */
    public function getCancellation(string $cancellationId, bool $lazy = false): ?Cancellation
    {
        /** @var Cancellation $cancellation */
        foreach ($this->cancellations as $cancellation) {
            if ($cancellation->getId() === $cancellationId) {
                if (!$lazy && $this instanceof UnzerParentInterface) {
                    /** @var AbstractUnzerResource $this*/
                    $this->getResource($cancellation);
                }
                return $cancellation;
            }
        }
        return null;
    }

    //</editor-fold>
}
