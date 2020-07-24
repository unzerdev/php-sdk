<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the Paypage.
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
 * @package  heidelpayPHP\test\integration\PaymentTypes
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Resources\CustomerFactory;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\test\BaseIntegrationTest;

class PaypageTest extends BaseIntegrationTest
{
    /**
     * Verify the Paypage resource for charge can be created with the mandatory parameters only.
     *
     * @test
     */
    public function minimalPaypageChargeShouldBeCreatableAndFetchable(): void
    {
        $paypage = new Paypage(100.0, 'EUR', self::RETURN_URL);
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->initPayPageCharge($paypage);
        $this->assertNotEmpty($paypage->getId());
    }

    /**
     * Verify the Paypage resource for charge can be created with all parameters.
     *
     * @test
     */
    public function maximumPaypageChargeShouldBeCreatable(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket = $this->createBasket();
        $customer = CustomerFactory::createCustomer('Max', 'Mustermann');
        $invoiceId = 'i'. self::generateRandomId();
        $paypage = (new Paypage(119.0, 'EUR', self::RETURN_URL))
            ->setLogoImage('https://dev.heidelpay.com/devHeidelpay_400_180.jpg')
            ->setFullPageImage('https://www.heidelpay.com/fileadmin/content/header-Imges-neu/Header_Phone_12.jpg')
            ->setShopName('My Test Shop')
            ->setShopDescription('Best shop in the whole world!')
            ->setTagline('Try and stop us from being awesome!')
            ->setOrderId($orderId)
            ->setTermsAndConditionUrl('https://www.heidelpay.com/en/')
            ->setPrivacyPolicyUrl('https://www.heidelpay.com/de/')
            ->setImprintUrl('https://www.heidelpay.com/it/')
            ->setHelpUrl('https://www.heidelpay.com/at/')
            ->setContactUrl('https://www.heidelpay.com/en/about-us/about-heidelpay/')
            ->setInvoiceId($invoiceId)
            ->setCard3ds(true)
            ->setEffectiveInterestRate(4.99)
            ->setCss([
                'shopDescription' => 'color: purple',
                'header' => 'background-color: red',
                'helpUrl' => 'color: blue',
                'contactUrl' => 'color: green',
            ]);
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->initPayPageCharge($paypage, $customer, $basket);
        $this->assertNotEmpty($paypage->getId());
        $this->assertEquals(4.99, $paypage->getEffectiveInterestRate());
        $payment = $paypage->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotNull($payment->getId());
        $this->assertNotEmpty($paypage->getRedirectUrl());
    }

    /**
     * Verify the Paypage resource for authorize can be created with the mandatory parameters only.
     *
     * @test
     */
    public function minimalPaypageAuthorizeShouldBeCreatableAndFetchable(): void
    {
        $paypage = new Paypage(100.0, 'EUR', self::RETURN_URL);
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->initPayPageAuthorize($paypage);
        $this->assertNotEmpty($paypage->getId());
    }

    /**
     * Verify the Paypage resource for authorize can be created with all parameters.
     *
     * @test
     */
    public function maximumPaypageAuthorizeShouldBeCreatable(): void
    {
        $orderId = 'o'. self::generateRandomId();
        $basket = $this->createBasket();
        $customer = CustomerFactory::createCustomer('Max', 'Mustermann');
        $invoiceId = 'i'. self::generateRandomId();
        $paypage = (new Paypage(119.0, 'EUR', self::RETURN_URL))
            ->setLogoImage('https://dev.heidelpay.com/devHeidelpay_400_180.jpg')
            ->setFullPageImage('https://www.heidelpay.com/fileadmin/content/header-Imges-neu/Header_Phone_12.jpg')
            ->setShopName('My Test Shop')
            ->setShopDescription('Best shop in the whole world!')
            ->setTagline('Try and stop us from being awesome!')
            ->setOrderId($orderId)
            ->setTermsAndConditionUrl('https://www.heidelpay.com/en/')
            ->setPrivacyPolicyUrl('https://www.heidelpay.com/de/')
            ->setImprintUrl('https://www.heidelpay.com/it/')
            ->setHelpUrl('https://www.heidelpay.com/at/')
            ->setContactUrl('https://www.heidelpay.com/en/about-us/about-heidelpay/')
            ->setInvoiceId($invoiceId)
            ->setCard3ds(true)
            ->setEffectiveInterestRate(4.99)
            ->setCss([
                'shopDescription' => 'color: purple',
                'header' => 'background-color: red',
                'helpUrl' => 'color: blue',
                'contactUrl' => 'color: green',
            ]);
        $paypage->addExcludeType(Card::getResourceName());
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->initPayPageAuthorize($paypage, $customer, $basket);
        $this->assertNotEmpty($paypage->getId());
        $this->assertEquals(4.99, $paypage->getEffectiveInterestRate());
        $this->assertEquals([Card::getResourceName()], $paypage->getExcludeTypes());
        $payment = $paypage->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotNull($payment->getId());
        $this->assertNotEmpty($paypage->getRedirectUrl());
    }

    /**
     * Validate paypage css can be set empty array.
     *
     * @test
     */
    public function cssShouldAllowForEmptyArray(): void
    {
        $paypage = new Paypage(100.0, 'EUR', self::RETURN_URL);
        $this->assertEmpty($paypage->getId());
        $paypage = $this->heidelpay->initPayPageAuthorize($paypage->setCss([]));
        $this->assertNotEmpty($paypage->getId());
    }
}
