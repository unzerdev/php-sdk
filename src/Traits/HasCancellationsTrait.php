<?php
/**
 * This trait adds the cancellation property to a class.
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
 * @package  heidelpay/mgw_sdk/traits
 */
namespace heidelpay\MgwPhpSdk\Traits;

use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;

trait HasCancellationsTrait
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
     * @return self
     */
    public function setCancellations(array $cancellations): self
    {
        $this->cancellations = $cancellations;
        return $this;
    }

    /**
     * @param Cancellation $cancellation
     * todo: return self
     */
    public function addCancellation(Cancellation $cancellation)
    {
        if ($this instanceof HeidelpayParentInterface) {
            $cancellation->setParentResource($this);
        }
        $this->cancellations[] = $cancellation;
    }
    //</editor-fold>
}
