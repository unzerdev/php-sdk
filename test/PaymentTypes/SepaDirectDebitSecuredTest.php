<?php
/**
 * These are integration tests to verify interface and functionality of the payment method sepa direct debit secured.
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
use heidelpay\NmgPhpSdk\PaymentTypes\SepaDirectDebitSecured;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class SepaDirectDebitSecuredTest extends BasePaymentTest
{
    /**
     * Verify Sepa Direct Debit secured can be created.
     *
     * @test
     * @return SepaDirectDebitSecured
     */
    public function sepaDirectDebitSecuredShouldBeCreatable(): SepaDirectDebitSecured
    {
        /** @var SepaDirectDebitSecured $directDebitSec */
        $directDebitSec = new SepaDirectDebitSecured('DE89370400440532013000');
        $directDebitSec = $this->heidelpay->createPaymentType($directDebitSec);
        $this->assertInstanceOf(SepaDirectDebitSecured::class, $directDebitSec);
        $this->assertNotNull($directDebitSec->getId());

        return $directDebitSec;
    }

    /**
     * Verify authorization is not allowed for sepa direct debit secured.
     *
     * @test
     * @param SepaDirectDebitSecured $directDebitSec
     * @depends sepaDirectDebitSecuredShouldBeCreatable
     */
    public function authorizeShouldThrowException(SepaDirectDebitSecured $directDebitSec)
    {
        $this->expectException(IllegalTransactionTypeException::class);
        $directDebitSec->authorize(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
    }

    /**
     * @test
     * @param SepaDirectDebitSecured $directDebitSec
     * @depends sepaDirectDebitSecuredShouldBeCreatable
     */
    public function directDebitShouldBeChargeable(SepaDirectDebitSecured $directDebitSec)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomer();
        $this->heidelpay->createCustomer($customer);

        $this->assertNotNull($customer->getId());

		$charge = $directDebitSec->charge(200.0, Currency::EUROPEAN_EURO, self::RETURN_URL, $customer);
		$this->assertNotNull($charge);
        $this->assertNotNull($charge->getId());
    }
}
