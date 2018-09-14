<?php
/**
 * These are integration tests to verify interface and functionality of the payment method GiroPay.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/NmgPhpSdk/test/integration
 */
namespace heidelpay\NmgPhpSdk\test\PaymentTypes;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\PaymentTypes\GiroPay;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class GiropayTest extends BasePaymentTest
{
    /**
     * Verify a GiroPay resource can be created.
     *
     * @test
     */
    public function giroPayShouldBeCreatable()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $this->assertNotNull($giropay->getId());
    }

    /**
     * Verify that an exception is thrown when giropay authorize is called.
     *
     * @test
     */
    public function giroPayShouldThrowExceptionOnAuthorize()
    {
        $this->expectException(IllegalTransactionTypeException::class);

        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);
        $giropay->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }
}
