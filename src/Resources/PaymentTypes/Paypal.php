<?php
/**
 * This represents the Paypal payment type.
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanRecur;
use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;

class Paypal extends BasePaymentType
{
    use CanAuthorize;
    use CanDirectCharge;
    use CanRecur;

    /** @var string|null $email */
    protected $email;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return Paypal
     */
    public function setEmail(string $email): Paypal
    {
        $this->email = $email;
        return $this;
    }

    //</editor-fold>
}
