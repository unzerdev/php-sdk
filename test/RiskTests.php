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

use heidelpay\NmgPhpSdk\Risk;

class RiskTests extends BasePaymentTest
{
    /**
     * Test risk creation.
     *
     * @test
     */
    public function riskObjectShouldBeCreatable()
    {
        $risk = new Risk($this->heidelpay);
        $this->assertEmpty($risk->getId());
        $risk->create();
        $this->assertNotEmpty($risk->getId());
    }
}
