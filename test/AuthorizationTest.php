<?php
/**
 * Description
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
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
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Authorization;

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
