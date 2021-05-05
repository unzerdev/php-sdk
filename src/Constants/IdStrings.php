<?php
/**
 * This file contains the different id strings to be handled within this SDK.
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
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\Constants
 */
namespace UnzerSDK\Constants;

class IdStrings
{
    // Transactions
    public const AUTHORIZE = 'aut';
    public const CANCEL = 'cnl';
    public const CHARGE = 'chg';
    public const PAYOUT = 'out';
    public const SHIPMENT = 'shp';

    // Payment Types
    public const ALIPAY = 'ali';
    public const APPLEPAY = 'apl';
    public const BANCONTACT = 'bct';
    public const CARD = 'crd';
    public const EPS = 'eps';
    public const GIROPAY = 'gro';
    public const HIRE_PURCHASE_DIRECT_DEBIT = 'hdd';
    public const IDEAL = 'idl';
    public const INSTALLMENT_SECURED = 'ins';
    public const INVOICE = 'ivc';
    public const INVOICE_FACTORING = 'ivf';
    public const INVOICE_GUARANTEED = 'ivg';
    public const INVOICE_SECURED = 'ivs';
    public const PAYMENT_PAGE = 'ppg';
    public const PAYPAL = 'ppl';
    public const PIS = 'pis';
    public const PREPAYMENT = 'ppy';
    public const PRZELEWY24 = 'p24';
    public const SEPA_DIRECT_DEBIT = 'sdd';
    public const SEPA_DIRECT_DEBIT_GUARANTEED = 'ddg';
    public const SEPA_DIRECT_DEBIT_SECURED = 'dds';
    public const SOFORT = 'sft';
    public const WECHATPAY = 'wcp';

    // Resources
    public const BASKET = 'bsk';
    public const CUSTOMER = 'cst';
    public const METADATA = 'mtd';
    public const PAYMENT = 'pay';

    public const WEBHOOK = 'whk';
    public const PAYMENT_TYPES = [
        self::ALIPAY,
        self::APPLEPAY,
        self::BANCONTACT,
        self::CARD,
        self::EPS,
        self::GIROPAY,
        self::HIRE_PURCHASE_DIRECT_DEBIT,
        self::IDEAL,
        self::INSTALLMENT_SECURED,
        self::INVOICE,
        self::INVOICE_FACTORING,
        self::INVOICE_GUARANTEED,
        self::INVOICE_SECURED,
        self::PAYMENT_PAGE,
        self::PAYPAL,
        self::PIS,
        self::PREPAYMENT,
        self::PRZELEWY24,
        self::SEPA_DIRECT_DEBIT,
        self::SEPA_DIRECT_DEBIT_GUARANTEED,
        self::SEPA_DIRECT_DEBIT_SECURED,
        self::SOFORT,
        self::WECHATPAY,
    ];
}
