<?php
/**
 * This class defines unit tests to verify functionality of the PayPage feature.
 *
 * Copyright (C) 2019 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class PayPageTest extends BaseUnitTest
{
    /**
     * Verify setter and getter work.
     *
     * @test
     *
     * @throws Exception
     */
    public function getterAndSetterWorkAsExpected()
    {
        // ----------- SET initial values ------------
        $paypage = new Paypage(123.4, 'EUR', 'https://docs.heidelpay.com');

        // ----------- VERIFY initial values ------------
        $this->assertEquals(123.4, $paypage->getAmount());
        $this->assertEquals('EUR', $paypage->getCurrency());
        $this->assertEquals('https://docs.heidelpay.com', $paypage->getReturnUrl());

        // meta
        $this->assertNull($paypage->getPaymentId());
        $this->assertNull($paypage->getPayment());
        $this->assertEquals(TransactionTypes::CHARGE, $paypage->getAction());
        $this->assertNull($paypage->getRedirectUrl());

        // layout and design
        $this->assertNull($paypage->getFullPageImage());
        $this->assertNull($paypage->getLogoImage());
        $this->assertNull($paypage->getShopDescription());
        $this->assertNull($paypage->getShopName());
        $this->assertNull($paypage->getTagline());

        // link urls
        $this->assertNull($paypage->getContactUrl());
        $this->assertNull($paypage->getHelpUrl());
        $this->assertNull($paypage->getImprintUrl());
        $this->assertNull($paypage->getPrivacyPolicyUrl());
        $this->assertNull($paypage->getTermsAndConditionUrl());

        // ----------- SET test values ------------
        $payment = (new Payment())->setId('my payment id');
        $paypage
            ->setAmount(321.0)
            ->setCurrency('CHF')
            ->setReturnUrl('my return url')
            ->setAction(TransactionTypes::AUTHORIZATION)
            ->setFullPageImage('full page image')
            ->setLogoImage('logo image')
            ->setShopDescription('my shop description')
            ->setShopName('my shop name')
            ->setTagline('my shops tag line')
            ->setContactUrl('my contact url')
            ->setHelpUrl('my help url')
            ->setImprintUrl('my imprint url')
            ->setPrivacyPolicyUrl('my privacy policy url')
            ->setTermsAndConditionUrl('my tac url')
            ->setPayment($payment)
            ->setRedirectUrl('https://redirect.url');

        // ----------- VERIFY test values ------------
        $this->assertEquals(321.0, $paypage->getAmount());
        $this->assertEquals('CHF', $paypage->getCurrency());
        $this->assertEquals('my return url', $paypage->getReturnUrl());

        // meta
        $this->assertEquals(TransactionTypes::AUTHORIZATION, $paypage->getAction());
        $this->assertEquals('my payment id', $paypage->getPaymentId());
        $this->assertSame($payment, $paypage->getPayment());
        $this->assertEquals('https://redirect.url', $paypage->getRedirectUrl());

        // layout and design
        $this->assertEquals('full page image', $paypage->getFullPageImage());
        $this->assertEquals('logo image', $paypage->getLogoImage());
        $this->assertEquals('my shop description', $paypage->getShopDescription());
        $this->assertEquals('my shop name', $paypage->getShopName());
        $this->assertEquals('my shops tag line', $paypage->getTagline());

        // link urls
        $this->assertEquals('my contact url', $paypage->getContactUrl());
        $this->assertEquals('my help url', $paypage->getHelpUrl());
        $this->assertEquals('my imprint url', $paypage->getImprintUrl());
        $this->assertEquals('my privacy policy url', $paypage->getPrivacyPolicyUrl());
        $this->assertEquals('my tac url', $paypage->getTermsAndConditionUrl());
    }
}
