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

        $this->assertNotNull($paypage->getPaypageId());
        $this->assertNotNull($paypage->getRedirectUrl());

        $this->assertStringContainsString($paypage->getPaypageId(), $paypage->getRedirectUrl());
    }

}
