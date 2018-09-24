<?php
/**
 * These are integration tests to verify interface and functionality of the payment method sepa direct debit guaranteed.
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
use heidelpay\NmgPhpSdk\Customer;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class SepaDirectDebitGuaranteedTest extends BasePaymentTest
{
    /**
     * Verify sepa direct debit guaranteed can be created.
     *
     * @test
     * @return SepaDirectDebitGuaranteed
     */
    public function sepaDirectDebitGuaranteedShouldBeCreatable(): SepaDirectDebitGuaranteed
    {
        /** @var SepaDirectDebitGuaranteed $directDebitGuaranteed */
        $directDebitGuaranteed = new SepaDirectDebitGuaranteed('DE89370400440532013000');
        $directDebitGuaranteed = $this->heidelpay->createPaymentType($directDebitGuaranteed);
        $this->assertInstanceOf(SepaDirectDebitGuaranteed::class, $directDebitGuaranteed);
        $this->assertNotNull($directDebitGuaranteed->getId());

        return $directDebitGuaranteed;
    }

    /**
     * Verify authorization is not allowed for sepa direct debit guaranteed.
     *
     * @test
     * @param SepaDirectDebitGuaranteed $directDebitGuaranteed
     * @depends sepaDirectDebitGuaranteedShouldBeCreatable
     */
    public function authorizeShouldThrowException(SepaDirectDebitGuaranteed $directDebitGuaranteed)
    {
        $this->expectException(IllegalTransactionTypeException::class);
        $directDebitGuaranteed->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * @test
     * @param SepaDirectDebitGuaranteed $directDebitGuaranteed
     * @depends sepaDirectDebitGuaranteedShouldBeCreatable
     */
    public function directDebitShouldBeChargeable(SepaDirectDebitGuaranteed $directDebitGuaranteed)
    {
        /** @var Customer $customer */
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);

        $this->assertNotNull($customer->getId());

		$charge = $directDebitGuaranteed->charge(200.0, Currency::EUROPEAN_EURO, self::RETURN_URL, $customer);
		$this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
    }
}
