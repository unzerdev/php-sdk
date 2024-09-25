<?php

namespace UnzerSDK\test\integration\Resources;

use UnzerSDK\Resources\EmbeddedResources\Paypage\AmountSettings;
use UnzerSDK\Resources\V2\Paypage;
use UnzerSDK\test\BaseIntegrationTest;

/**
 * @group CC-1316
 * @group CC-1578
 */
class LinkpayV2Test extends BaseIntegrationTest
{
    /**
     * @test
     */
    public function createMinimumPaypageTest()
    {
        $paypage = new Paypage(9.99, 'EUR', 'charge');
        $paypage->setType('linkpay');

        $this->assertNull($paypage->getId());
        $this->assertNull($paypage->getQrCodeSvg());
        $this->assertNull($paypage->getRedirectUrl());

        // when
        $this->getUnzerObject()->createPaypage($paypage);

        // then
        $this->assertCreatedPaypage($paypage);
        $this->assertEquals('linkpay', $paypage->getType());
        $this->assertNotNull($paypage->getQrCodeSvg());
    }

    /**
     * @test
     */
    public function createLinkpayWithSpecificFields()
    {
        $paypage = new Paypage(null, 'EUR', 'charge');
        $paypage->setType('linkpay');
        $paypage->setAlias($this->generateRandomId());
        $paypage->setExpiresAt(new \DateTime());
        $paypage->setAmountSettings(new AmountSettings(10, 100));

        $this->assertNull($paypage->getId());
        $this->assertNull($paypage->getRedirectUrl());

        $this->getUnzerObject()->createPaypage($paypage);

        $this->assertCreatedPaypage($paypage);

        $this->assertEquals('linkpay', $paypage->getType());
    }

    /**
     * @param Paypage $paypage
     * @return void
     */
    public function assertCreatedPaypage(Paypage $paypage): void
    {
        $this->assertNotNull($paypage->getId());
        $this->assertNotNull($paypage->getRedirectUrl());
        if ($paypage->getAlias() == null) {
            $this->assertStringContainsString($paypage->getId(), $paypage->getRedirectUrl());
        } else {
            $this->assertStringContainsString($paypage->getAlias(), $paypage->getRedirectUrl());
        }
    }
}
