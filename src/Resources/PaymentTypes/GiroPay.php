<?php
/**
 * This represents the GiroPay payment type.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/PaymentTypes
 */
namespace heidelpay\NmgPhpSdk\Resources\PaymentTypes;

class GiroPay extends BasePaymentType
{
    /**
     * GiroPay constructor.
     */
    public function __construct()
    {
        $this->setChargeable(true);

        parent::__construct();
    }
}
