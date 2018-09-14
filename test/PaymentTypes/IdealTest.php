<?php
/**
 * These are integration tests to verify interface and functionality of the payment method Ideal.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/test/integration
 */
namespace heidelpay\NmgPhpSdk\test\PaymentTypes;

use heidelpay\NmgPhpSdk\PaymentTypes\Ideal;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class IdealTest extends BasePaymentTest
{
    /**
     * Verify Ideal payment type is creatable.
     *
     * @test
     */
    public function idealShouldBeCreatable()
    {
        /** @var Ideal $ideal */
        $ideal = new Ideal();
        $ideal->setBankName('RABONL2U');
        $this->heidelpay->createPaymentType($ideal);
        $this->assertInstanceOf(Ideal::class, $ideal);
        $this->assertNotNull($ideal->getId());
    }
}
