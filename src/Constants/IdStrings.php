<?php
/**
 * This file contains the different id strings to be handled within this SDK.
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
 * @package  heidelpayPHP\Constants
 */
namespace heidelpayPHP\Constants;

class IdStrings
{
    // Transactions
    public const CHARGE = 'chg';
    public const AUTHORIZE = 'aut';
    public const CANCEL = 'cnl';
    public const SHIPMENT = 'shp';
    public const PAYOUT = 'out';

    // Payment Types
    public const CARD = 'crd';
    public const GIROPAY = 'gro';
    public const IDEAL = 'idl';
    public const INVOICE = 'ivc';
    public const INVOICE_GUARANTEED = 'ivg';
    public const PAYPAL = 'ppl';
    public const PREPAYMENT = 'ppy';
    public const PRZELEWY24 = 'p24';
    public const SEPA_DIRECT_DEBIT_GUARANTEED = 'ddg';
    public const SEPA_DIRECT_DEBIT = 'sdd';
    public const SOFORT = 'sft';
    public const PIS = 'pis';
    public const EPS = 'eps';
    public const ALIPAY = 'ali';
    public const WECHATPAY = 'wcp';
    public const INVOICE_FACTORING = 'ivf';
    public const HIRE_PURCHASE_DIRECT_DEBIT = 'hdd';
    public const PAYMENT_PAGE = 'ppg';

    // Resources
    public const BASKET = 'bsk';
    public const WEBHOOK = 'whk';
    public const PAYMENT = 'pay';
    public const CUSTOMER = 'cst';
    public const METADATA = 'mtd';
    
    public const PAYMENT_TYPES = [
        self::CARD,
        self::GIROPAY,
        self::IDEAL,
        self::INVOICE,
        self::INVOICE_GUARANTEED,
        self::PAYPAL,
        self::PREPAYMENT,
        self::PRZELEWY24,
        self::SEPA_DIRECT_DEBIT_GUARANTEED,
        self::SEPA_DIRECT_DEBIT,
        self::SOFORT,
        self::PIS,
        self::EPS,
        self::ALIPAY,
        self::WECHATPAY,
        self::INVOICE_FACTORING,
        self::PAYMENT_PAGE,
        self::HIRE_PURCHASE_DIRECT_DEBIT
    ];
}
