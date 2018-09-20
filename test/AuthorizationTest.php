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

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;

class AuthorizationTest extends BasePaymentTest
{
    /**
     * Verify heidelpay object can perform an authorization based on the paymentTypeId.
     *
     * @test
     */
    public function authorizeWithTypeId()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCard());

        /** @var Authorization $authorize */
        $authorize = $this->heidelpay->authorizeWithPaymentTypeId(
            $card->getId(),
            100.0,
            Currency::EUROPEAN_EURO,
            self::RETURN_URL
        );

        $this->assertNotNull($authorize);
        $this->assertNotNull($authorize->getId());
    }
}
