<?php

namespace UnzerSDK\test\integration\Resources;

use UnzerSDK\Resources\V2\Paypage;
use UnzerSDK\test\BaseIntegrationTest;

class PaypageV2Test extends BaseIntegrationTest
{
    /**
     * @test
     */
    public function createMinimumPaypageTest()
    {
        $paypage = new Paypage(9.99, 'EUR', 'charge');

        $this->assertNull($paypage->getPaypageId());
        $this->assertNull($paypage->getRedirectUrl());

        $this->getUnzerObject()->createPaypage($paypage);

        $this->assertCreatedPaypage($paypage);
    }

    /**
     * @test
     */
    public function createPaypageWithOptionalProperties()
    {
        $paypage = new Paypage(9.99, 'EUR', 'charge');
        $paypage->setType('hosted');
        $paypage->setRecurrenceType('unscheduled');
        $paypage->setLogoImage('logoImage');
        $paypage->setShopName('shopName');
        $paypage->setOrderId('orderId');
        $paypage->setInvoiceId('invoiceId');
        $paypage->setPaymentReference('paymentReference');

        $this->getUnzerObject()->createPaypage($paypage);

        $this->assertCreatedPaypage($paypage);
        //TODO: fetch paypage and compare properties.
    }

    /**
     * @param Paypage $paypage
     * @return void
     */
    public function assertCreatedPaypage(Paypage $paypage): void
    {
        $this->assertNotNull($paypage->getPaypageId());
        $this->assertNotNull($paypage->getRedirectUrl());
        $this->assertStringContainsString($paypage->getPaypageId(), $paypage->getRedirectUrl());
    }

}
