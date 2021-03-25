<?php

namespace UnzerSDK\test\unit\Resources\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\Applepay;
use UnzerSDK\test\BasePaymentTest;

class ApplePayTest extends BasePaymentTest
{
    /**
     * Verify getters and setters work as expected.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected(): void
    {
        $applePay = new Applepay('EC_v1', 'data1', 'signature');
        $this->assertNull($applePay->getApplicationPrimaryAccountNumber());
        $this->assertNull($applePay->getApplicationExpirationDate());
        $this->assertNull($applePay->getCurrencyCode());
        $this->assertNull($applePay->getMethod());
        $this->assertEquals(0, $applePay->getTransactionAmount());
        $this->assertEquals('data1', $applePay->getData());
        $this->assertEquals('signature', $applePay->getSignature());
        $this->assertEquals('EC_v1', $applePay->getVersion());

        // Call setters
        $applePay->setApplicationExpirationDate('07/2020');
        $applePay->setApplicationPrimaryAccountNumber('123456789');
        $applePay->setCurrencyCode('EUR');
        $applePay->setData('some-Data');
        $applePay->setMethod('apple-pay');
        $applePay->setSignature('mySignature');
        $applePay->setTransactionAmount(100.19);
        $applePay->setVersion('EC_v1');

        $this->assertEquals('07/2020', $applePay->getApplicationExpirationDate());
        $this->assertEquals('123456789', $applePay->getApplicationPrimaryAccountNumber());
        $this->assertEquals('EUR', $applePay->getCurrencyCode());
        $this->assertEquals('some-Data', $applePay->getData());
        $this->assertEquals('apple-pay', $applePay->getMethod());
        $this->assertEquals('mySignature', $applePay->getSignature());
        $this->assertEquals(100.19, $applePay->getTransactionAmount());
        $this->assertEquals('EC_v1', $applePay->getVersion());

        $applePay->setApplicationExpirationDate(null);
        $applePay->setApplicationPrimaryAccountNumber(null);
        $applePay->setCurrencyCode(null);
        $applePay->setData(null);
        $applePay->setMethod(null);
        $applePay->setSignature(null);
        $applePay->setTransactionAmount(0);
        $applePay->setVersion(null);

        $this->assertNull($applePay->getApplicationExpirationDate());
        $this->assertNull($applePay->getApplicationPrimaryAccountNumber());
        $this->assertNull($applePay->getCurrencyCode());
        $this->assertNull($applePay->getData());
        $this->assertNull($applePay->getMethod());
        $this->assertNull($applePay->getSignature());
        $this->assertEquals(0, $applePay->getTransactionAmount());
        $this->assertNull($applePay->getVersion());
    }
}
