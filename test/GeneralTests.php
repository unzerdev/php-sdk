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
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Exceptions\IllegalKeyException;
use heidelpay\NmgPhpSdk\Heidelpay;

class GeneralTests extends BasePaymentTest
{

    /**
     * @test
     */
    public function heidelpayObjectShouldThrowExceptionWhenKeyIsPublic()
    {
        $this->expectException(IllegalKeyException::class);
        $this->heidelpay = new Heidelpay(BasePaymentTest::PUBLIC_KEY);
    }

}
