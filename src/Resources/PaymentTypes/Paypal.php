<?php
/**
 * This represents the Paypal payment type.
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
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

use RuntimeException;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\Recurring;
use UnzerSDK\Traits\CanRecur;
use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;

class Paypal extends BasePaymentType
{
    use CanAuthorize;
    use CanDirectCharge;
    use CanRecur {
        activateRecurring as traitActivateRecurring;
    }

    /** @var string|null $email */
    protected $email;

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

    /**
     * Activates recurring payment for Paypal.
     *
     * @param string     $returnUrl      The URL to which the customer gets redirected in case of a 3ds transaction
     * @param null|mixed $recurrenceType Recurrence type used for recurring payment.
     *
     * @return Recurring
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function activateRecurring($returnUrl, $recurrenceType = null): Recurring
    {
        return $this->traitActivateRecurring($returnUrl, $recurrenceType);
    }
}
