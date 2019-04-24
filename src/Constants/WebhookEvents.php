<?php
/**
 * This file contains the different web hook events which can be subscribed.
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
 * @package  heidelpayPHP/constants
 */
namespace heidelpayPHP\Constants;

class WebhookEvents
{
    // all events
    const ALL = 'all';

    // authorize events
    const AUTHORIZE = 'authorize';
    const AUTHORIZE_SUCCEEDED = 'authorize.succeeded';
    const AUTHORIZE_FAILED = 'authorize.failed';
    const AUTHORIZE_PENDING = 'authorize.pending';
    const AUTHORIZE_EXPIRED = 'authorize.expired';
    const AUTHORIZE_CANCELED = 'authorize.canceled';

    // charge events
    const CHARGE = 'charge';
    const CHARGE_SUCCEEDED = 'charge.succeeded';
    const CHARGE_FAILED = 'charge.failed';
    const CHARGE_PENDING = 'charge.pending';
    const CHARGE_EXPIRED = 'charge.expired';
    const CHARGE_CANCELED = 'charge.canceled';

    // chargeback events
    const CHARGEBACK = 'chargeback';

    // types events
    const TYPES = 'types';

    // customer events
    const CUSTOMER = 'customer';
    const CUSTOMER_CREATED = 'customer.created';
    const CUSTOMER_DELETED = 'customer.deleted';
    const CUSTOMER_UPDATED = 'customer.updated';

    // payment events
    const PAYMENT_PENDING = 'payment.pending';
    const PAYMENT_COMPLETED = 'payment.completed';
    const PAYMENT_CANCELED = 'payment.canceled';
    const PAYMENT_PARTLY = 'payment.partly';
    const PAYMENT_PAYMENT_REVIEW = 'payment.payment_review';
    const PAYMENT_CHARGEBACK = 'payment.chargeback';

    // shipment events
    const SHIPMENT = 'shipment';

    const ALLOWED_WEBHOOKS = [
        self::ALL,
        self::AUTHORIZE,
        self::AUTHORIZE_SUCCEEDED,
        self::AUTHORIZE_FAILED,
        self::AUTHORIZE_PENDING,
        self::AUTHORIZE_EXPIRED,
        self::AUTHORIZE_CANCELED,
        self::CHARGE,
        self::CHARGE_SUCCEEDED,
        self::CHARGE_FAILED,
        self::CHARGE_PENDING,
        self::CHARGE_EXPIRED,
        self::CHARGE_CANCELED,
        self::CHARGEBACK,
        self::TYPES,
        self::CUSTOMER,
        self::CUSTOMER_CREATED,
        self::CUSTOMER_DELETED,
        self::CUSTOMER_UPDATED,
        self::PAYMENT_PENDING,
        self::PAYMENT_COMPLETED,
        self::PAYMENT_CANCELED,
        self::PAYMENT_PARTLY,
        self::PAYMENT_PAYMENT_REVIEW,
        self::PAYMENT_CHARGEBACK,
        self::SHIPMENT
    ];
}
