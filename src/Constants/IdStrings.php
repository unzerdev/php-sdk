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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/constants
 */
namespace heidelpayPHP\Constants;

class IdStrings
{
    // Transactions
    const CHARGE = 'chg';
    const AUTHORIZE = 'aut';
    const CANCEL = 'cnl';
    const SHIPMENT = 'shp';

    // Payment Types
    const CARD = 'crd';
    const GIROPAY = 'gro';
    const IDEAL = 'idl';
    const INVOICE = 'ivc';
    const INVOICE_GUARANTEED = 'ivg';
    const PAYPAL = 'ppl';
    const PREPAYMENT = 'ppy';
    const PRZELEWY24 = 'p24';
    const SEPA_DIRECT_DEBIT_GUARANTEED = 'ddg';
    const SEPA_DIRECT_DEBIT = 'sdd';
    const SOFORT = 'sft';
    const PIS = 'pis';
    const EPS = 'eps';
    const ALIPAY = 'ali';
    const WECHATPAY = 'wcp';
    const INVOICE_FACTORING = 'ivf';

    // Resources
    const BASKET = 'bsk';
    const WEBHOOK = 'whk';
    const PAYMENT = 'pay';
    const CUSTOMER = 'cst';
    const METADATA = 'mtd';
    
    const PAYMENT_TYPES = [
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
        self::INVOICE_FACTORING
    ];
}
