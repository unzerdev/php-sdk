<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and
 * functionality of the payment method twint.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Twint;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;
use UnzerSDK\test\integration\Traits\OnlineTransferTestTrait;

class TwintTest extends BaseIntegrationTest
{
    protected const testClass = Twint::class;

    protected function setUp(): void
    {
//        $this->markTestSkipped();
        parent::setUp();
    }

    /**
     * Verify twint can be created.
     *
     * @test
     *
     * @return Twint
     */
    public function twintShouldBeCreatableAndFetchable(): Twint
    {
        $paymentType = $this->unzer->createPaymentType($this->createTypeInstance());
        $this->assertInstanceOf(self::testClass, $paymentType);
        $this->assertNotNull($paymentType->getId());

        /** @var Twint $fetchedTwint */
        $fetchedTwint = $this->unzer->fetchPaymentType($paymentType->getId());
        $this->assertInstanceOf(self::testClass, $fetchedTwint);
        $this->assertEquals($paymentType->expose(), $fetchedTwint->expose());
        $this->assertNotEmpty($fetchedTwint->getGeoLocation()->getClientIp());

        return $fetchedTwint;
    }

    /**
     * Verify twint is chargeable.
     *
     * @test
     *
     * @param Twint $twint
     *
     * @return Charge
     *
     * @depends twintShouldBeCreatableAndFetchable
     */
    public function twintShouldBeAbleToCharge(BasePaymentType $twint): Charge
    {
        $charge = $this->unzer->charge(100.0, 'EUR', $twint, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getRedirectUrl());

        return $charge;
    }

    /**
     * Verify twint is not authorizable.
     *
     * @test
     *
     * @param Twint $twint
     *
     * @depends twintShouldBeCreatableAndFetchable
     */
    public function twintShouldNotBeAuthorizable(BasePaymentType $twint): void
    {
        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->unzer->authorize(100.0, 'EUR', $twint, self::RETURN_URL);
    }

    /**
     * @return Twint
     */
    public function createTypeInstance(): Twint
    {
        $class = self::testClass;
        return new $class();
    }
}
