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

use heidelpay\NmgPhpSdk\PaymentTypes\SepaDirectDebitSecured;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

class SepaDirectDebitSecuredTest extends BasePaymentTest
{
    /**
     * Verify Sepa Direct Debit secured can be created.
     *
     * @test
     */
    public function sepaDirectDebitSecuredShouldBeCreatable()
    {
        /** @var SepaDirectDebitSecured $directDebitSec */
        $directDebitSec = new SepaDirectDebitSecured('DE89370400440532013000');
        $directDebitSec = $this->heidelpay->createPaymentType($directDebitSec);
        $this->assertInstanceOf(SepaDirectDebitSecured::class, $directDebitSec);
        $this->assertNotNull($directDebitSec->getId());
    }
}
