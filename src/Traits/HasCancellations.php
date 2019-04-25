<?php
/**
 * This trait adds the cancellation property to a class.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/traits
 */
namespace heidelpayPHP\Traits;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Interfaces\HeidelpayParentInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
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
        if ($this instanceof HeidelpayParentInterface) {
            $cancellation->setParentResource($this);
        }
        $this->cancellations[] = $cancellation;
        return $this;
    }

    /**
     * Return specific Cancellation object or null if it does not exist.
     *
     * @param string  $cancellationId
     * @param boolean $lazy
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function getCancellation($cancellationId, $lazy = false)
    {
        /** @var Cancellation $cancellation */
        foreach ($this->cancellations as $cancellation) {
            if ($cancellation->getId() === $cancellationId) {
                if (!$lazy && $this instanceof HeidelpayParentInterface) {
                    /** @var AbstractHeidelpayResource $this*/
                    $this->getResource($cancellation);
                }
                return $cancellation;
            }
        }
        return null;
    }

    //</editor-fold>
}
