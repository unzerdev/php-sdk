<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method paypal.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\OpenBanking;
use UnzerSDK\test\BaseIntegrationTest;

class OpenBankingTest extends BaseIntegrationTest
{
    /**
     * Verify OpenBanking payment type can be created and fetched.
     *
     * @test
     *
     * @return BasePaymentType
     */
    public function openBankingShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $openBanking = $this->unzer->createPaymentType(new OpenBanking('DE'));
        $this->assertInstanceOf(OpenBanking::class, $openBanking);
        $this->assertNotEmpty($openBanking->getId());

        $fetchedOpenBanking = $this->unzer->fetchPaymentType($openBanking->getId());
        $this->assertInstanceOf(OpenBanking::class, $fetchedOpenBanking);
        $this->assertNotSame($openBanking, $fetchedOpenBanking);
        $this->assertEquals($openBanking->expose(), $fetchedOpenBanking->expose());

        return $fetchedOpenBanking;
    }



    /**
     * Verify OpenBanking can authorize.
     *
     * @test
     *
     * @depends openBankingShouldBeCreatableAndFetchable
     *
     * @param OpenBanking $openBanking
     */
    public function openBankingShouldBeAuthorizable(OpenBanking $openBanking): void
    {
        $authorization = $openBanking->authorize(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($authorization);
        $this->assertNotEmpty($authorization->getId());
        $this->assertNotEmpty($authorization->getRedirectUrl());

        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());
    }

    /**
     * Verify OpenBanking can charge.
     *
     * @test
     *
     * @depends openBankingShouldBeCreatableAndFetchable
     *
     * @param OpenBanking $openBanking
     */
    public function openBankingShouldBeChargeable(OpenBanking $openBanking): void
    {
        $charge = $openBanking->charge(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
    }


}
