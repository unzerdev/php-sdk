<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method sepa direct debit guaranteed.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

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
