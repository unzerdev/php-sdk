<?php

/**
 * This file contains the different recurrence types.
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
 * @package  UnzerSDK\Constants
 */
namespace UnzerSDK\Constants;

class RecurrenceTypes
{
    /** @var string  Recurring with a defined interval and a defined amount.*/
    public const SCHEDULED = 'scheduled';

    /** @var string  Recurring with a undefined interval and/or an undefined amount.*/
    public const UNSCHEDULED = 'unscheduled';

    /** @var string If the payment type should be used again for future transactions.*/
    public const ONE_CLICK = 'oneclick';
}
