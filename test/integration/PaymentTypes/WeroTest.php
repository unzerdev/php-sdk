<?php

/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpDocMissingThrowsInspection */

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Wero;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

class WeroTest extends BaseIntegrationTest
{
    /**
     * @test
     *
     * @return BasePaymentType
     */
    public function weroShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $wero = $this->unzer->createPaymentType(new Wero());
        $this->assertInstanceOf(Wero::class, $wero);
        $this->assertNotEmpty($wero->getId());

        $fetched = $this->unzer->fetchPaymentType($wero->getId());
        $this->assertInstanceOf(Wero::class, $fetched);
        $this->assertNotSame($wero, $fetched);
        $this->assertEquals($wero->expose(), $fetched->expose());

        return $fetched;
    }

    /**
     * Verify Wero can authorize.
     *
     * @test
     *
     * @depends weroShouldBeCreatableAndFetchable
     */
    public function weroShouldBeAuthorizable(Wero $wero): void
    {
        $authorization = new Authorization(100.0, 'EUR', self::RETURN_URL);
        $authorization = $this->unzer->performAuthorization($authorization, $wero);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertNotEmpty($authorization->getRedirectUrl());

        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());
    }

    /**
     * Verify Wero can charge.
     *
     * @test
     *
     * @depends weroShouldBeCreatableAndFetchable
     */
    public function weroShouldBeChargeable(Wero $wero): void
    {
        $charge = new Charge(100.0, 'EUR', self::RETURN_URL);
        $charge = $this->unzer->performCharge($charge, $wero);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        $fetched = $this->unzer->fetchChargeById($charge->getPayment()->getId(), $charge->getId());
        $this->assertEquals($charge->setCard3ds(false)->expose(), $fetched->expose());
    }
}
