<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\MgwPhpSdk\Constants;

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
}
