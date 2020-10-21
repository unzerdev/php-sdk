<?php
/**
 * This represents the Bancontact payment type.
 *
 * Copyright (C) 2020 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  David Owusu <development@heidelpay.com>
 * @author  Florian Evertz <development@heidelpay.com>
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Bancontact extends BasePaymentType
{
    use CanDirectCharge;

    /** @var string|null $holder */
    protected $holder;

    /**
     * Set the holder of the account.
     *
     * @param string|null $holder
     *
     * @return Bancontact
     */
    public function setHolder(?string $holder): Bancontact
    {
        $this->holder = $holder;
        return $this;
    }

    /**
     * Returns the holder of the account.
     *
     * @return string|null
     */
    public function getHolder(): ?string
    {
        return $this->holder;
    }
}
