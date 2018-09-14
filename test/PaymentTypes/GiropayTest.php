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
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

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

    /**
     * Verify that GiroPay is chargeable.
     *
     * @test
     */
    public function giroPayShouldBeChargeable()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);

        /** @var Charge $charge */
        $charge = $giropay->charge(1.0, currency::EUROPEAN_EURO, self::RETURN_URL);

        $this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());
    }

    /**
     * Verify a GiroPay object can be fetched from the api.
     *
     * @test
     */
    public function giroPayCanBeFetched()
    {
        /** @var GiroPay $giropay */
        $giropay = new GiroPay();
        $giropay = $this->heidelpay->createPaymentType($giropay);

        $fetchedGiropay = $this->heidelpay->fetchPaymentType($giropay->getId());
        $this->assertInstanceOf(GiroPay::class, $fetchedGiropay);
        $this->assertEquals($giropay->getId(), $fetchedGiropay->getId());
    }
}
