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

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\PaymentTypes\Ideal;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class IdealTest extends BasePaymentTest
{
    // todo: Fetch list of banks first. then set the bank name and update ideal type.
    // todo: Add test verifying error on illegal bankname.

    /**
     * Verify Ideal payment type is creatable.
     *
     * @test
     * @return Ideal
     */
    public function idealShouldBeCreatable(): Ideal
    {
        /** @var Ideal $ideal */
        $ideal = new Ideal();
        $ideal->setBankName('RABONL2U');
        $this->heidelpay->createPaymentType($ideal);
        $this->assertInstanceOf(Ideal::class, $ideal);
        $this->assertNotNull($ideal->getId());

        return $ideal;
    }

    /**
     * Verify that ideal is not authorizable
     *
     * @test
     * // todo fix when ideal operation is correctly defined.
     * @param Ideal $ideal
     * @depends idealShouldBeCreatable
     */
    public function idealShouldThrowExceptionOnAuthorize(Ideal $ideal)
    {
        $this->expectException(IllegalTransactionTypeException::class);
        $ideal->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * Verify that ideal payment type is chargeable.
     *
     * @test
     * @depends idealShouldBeCreatable
     * @param Ideal $ideal
     */
    public function idealShouldBeChargeable(Ideal $ideal)
    {
		$charge = $ideal->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
		$this->assertNotNull($charge);
		$this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());
    }

    /**
     * Verify ideal payment type can be fetched.
     *
     * @test
     * @depends idealShouldBeCreatable
     * @param Ideal $ideal
     */
    public function idealTypeCanBeFetched(Ideal $ideal)
    {
		$fetchedIdeal = $this->heidelpay->fetchPaymentType($ideal->getId());
        $this->assertInstanceOf(Ideal::class, $fetchedIdeal);
        $this->assertEquals($ideal->getId(), $fetchedIdeal->getId());
    }
}
