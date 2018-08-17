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
namespace heidelpay\NmgPhpSdk\Constants;

class PaymentState
{
    const STATE_PENDING = 0;
    const STATE_COMPLETED = 1;
    const STATE_CANCELED = 2;
    const STATE_PARTLY = 3;
    const STATE_PAYMENT_REVIEW = 4;
    const STATE_CHARGEBACK = 5;
}
