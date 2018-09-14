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
namespace heidelpay\NmgPhpSdk\PaymentTypes;

class GiroPay extends BasePaymentType
{
    /**
     * GiroPay constructor.
     */
    public function __construct()
    {
        $this->setAuthorizable(true)
             ->setChargeable(true);

        parent::__construct();
    }

    //<editor-fold desc="Overridable Methods">
    /**
     * {@inheritDoc}
     */
    public function getResourcePath()
    {
        return 'types/giropay';
    }
    //</editor-fold>
}
