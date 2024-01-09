<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality
 * of the Google Pay payment method.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace PaymentTypes;

use UnzerSDK\Constants\ApiResponseCodes;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\EmbeddedResources\GooglePay\IntermediateSigningKey;
use UnzerSDK\Resources\EmbeddedResources\GooglePay\SignedKey;
use UnzerSDK\Resources\EmbeddedResources\GooglePay\SignedMessage;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\PaymentTypes\Googlepay;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;
use UnzerSDK\test\Fixtures\JsonProvider;

class GooglePayTest extends BaseIntegrationTest
{
    /**
     * Verify that googlepay payment type resource can be created.
     *
     * @test
     *
     * @return BasePaymentType
     */
    public function googlePayShouldBeCreatable(): BasePaymentType
    {
        $googlePay = new Googlepay();
        $this->assertNull($googlePay->getId());

        $geoLocation = $googlePay->getGeoLocation();
        $this->assertNull($geoLocation->getClientIp());
        $this->assertNull($geoLocation->getCountryCode());

        // Create Googlepay object from token.
        $googlePayToken = json_decode(JsonProvider::getJsonFromFile('googlePay/googlepayToken.json'));
        $googlePayToken->intermediateSigningKey->signedKey = json_decode($googlePayToken->intermediateSigningKey->signedKey);
        $googlePayToken->signedMessage = json_decode($googlePayToken->signedMessage);

        $signedKey = (new SignedKey())
            ->setKeyValue($googlePayToken->intermediateSigningKey->signedKey->keyValue)
            ->setKeyExpiration($googlePayToken->intermediateSigningKey->signedKey->keyExpiration);

        $intermediateSigningKey = (new IntermediateSigningKey())
            ->setSignedKey($signedKey)
            ->setSignatures($googlePayToken->intermediateSigningKey->signatures);

        $protocolVersion = $googlePayToken->protocolVersion;
        $encryptedMessage = $googlePayToken->signedMessage->encryptedMessage;
        $ephemeralPublicKey = $googlePayToken->signedMessage->ephemeralPublicKey;
        $tag = $googlePayToken->signedMessage->tag;

        $signedMessage = (new SignedMessage())
            ->setEncryptedMessage($encryptedMessage)
            ->setEphemeralPublicKey($ephemeralPublicKey)
            ->setTag($tag);

        $googlePay->setSignature($googlePayToken->signature)
            ->setIntermediateSigningKey($intermediateSigningKey)
            ->setProtocolVersion($protocolVersion)
            ->setSignedMessage($signedMessage);

        /** @var Googlepay $googlePay */
        $googlePay = $this->unzer->createPaymentType($googlePay);

        $this->assertInstanceOf(Googlepay::class, $googlePay);
        $this->assertNotNull($googlePay->getId());
        $this->assertSame($this->unzer, $googlePay->getUnzerObject());

        $geoLocation = $googlePay->getGeoLocation();
        $this->assertNotEmpty($geoLocation->getClientIp());
        //        $this->assertNotEmpty($geoLocation->getCountryCode());

        return $googlePay;
    }

    /**
     * Verify that authorization can be performed with Google Pay.
     *
     * @test
     *
     * @depends googlePayShouldBeCreatable
     *
     * @param mixed $type
     */
    public function googlepayCanPerformAuthorizationAndCreatesPayment($type): Authorization
    {
        $authorization = $this->getUnzerObject()
            ->performAuthorization(
                new Authorization(99.99, 'EUR', self::RETURN_URL),
                $type
            );

        // verify authorization has been created
        $this->assertNotNull($authorization->getId());

        // verify payment object has been created
        $payment = $authorization->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        // verify resources are linked properly
        $this->assertSame($authorization, $payment->getAuthorization());
        $this->assertSame($type, $payment->getPaymentType());

        // verify the payment object has been updated properly
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        return $authorization;
    }

    /**
     * Verify the googlepay can perform charges and creates a payment object doing so.
     *
     * @test
     *
     * @depends googlePayShouldBeCreatable
     *
     * @param mixed $type
     */
    public function googlepayCanPerformChargeAndCreatesPaymentObject($type): void
    {
        $charge = $this->getUnzerObject()
            ->performCharge(
                new Charge(99.99, 'EUR', self::RETURN_URL),
                $type
            );

        $fetchedType = $this->unzer->fetchPaymentType($type->getId());

        // verify charge has been created
        $this->assertNotNull($charge->getId());

        // verify payment object has been created
        $payment = $charge->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());

        // verify resources are linked properly
        $this->assertEquals($charge->expose(), $payment->getCharge($charge->getId())->expose());
        $this->assertSame($fetchedType, $payment->getPaymentType());

        // verify the payment object has been updated properly
        $this->assertAmounts($payment, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($payment->isCompleted());
    }

    /**
     * Verify that a googlepay object can be fetched from the api using its id.
     *
     * @test
     *
     * @depends googlePayShouldBeCreatable
     *
     * @param mixed $type
     */
    public function googlepayCanBeFetched($type): void
    {
        $this->assertNotNull($type->getId());

        /** @var Googlepay $fetchedGooglepay */
        $fetchedGooglepay = $this->unzer->fetchPaymentType($type->getId());
        $this->assertNotNull($fetchedGooglepay->getId());
        $this->assertEquals($type->getNumber(), $fetchedGooglepay->getNumber());
        $this->assertEquals($type->getExpiryDate(), $fetchedGooglepay->getExpiryDate());
    }

    /**
     * Verify the googlepay can charge the full amount of the authorization and the payment state is updated accordingly.
     *
     * @test
     */
    public function fullChargeAfterAuthorize(): void
    {
        $googlepay = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay = $this->unzer->createPaymentType($googlepay);

        $authorization = $googlepay->authorize(1.0, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment = $authorization->getPayment();

        // pre-check to verify changes due to fullCharge call
        $this->assertAmounts($payment, 1.0, 0.0, 1.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge     = $this->unzer->chargeAuthorization($payment->getId());
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 1.0, 1.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify the googlepay can charge part of the authorized amount and the payment state is updated accordingly.
     *
     * @test
     */
    public function partialChargeAfterAuthorization(): void
    {
        $googlepay          = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay          = $this->unzer->createPaymentType($googlepay);
        $authorization = $this->unzer->authorize(
            100.0,
            'EUR',
            $googlepay,
            self::RETURN_URL,
            null,
            null,
            null,
            null,
            false
        );

        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge   = $this->unzer->chargeAuthorization($payment->getId(), 20);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $charge   = $this->unzer->chargeAuthorization($payment->getId(), 20);
        $payment2 = $charge->getPayment();
        $this->assertAmounts($payment2, 60.0, 40.0, 100.0, 0.0);
        $this->assertTrue($payment2->isPartlyPaid());

        $charge   = $this->unzer->chargeAuthorization($payment->getId(), 60);
        $payment3 = $charge->getPayment();
        $this->assertAmounts($payment3, 00.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment3->isCompleted());
    }

    /**
     * Verify that an exception is thrown when trying to charge more than authorized.
     *
     * @test
     */
    public function exceptionShouldBeThrownWhenChargingMoreThenAuthorized(): void
    {
        $googlepay          = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay          = $this->unzer->createPaymentType($googlepay);
        $authorization = $googlepay->authorize(100.0000, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment       = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge   = $this->unzer->chargeAuthorization($payment->getId(), 50);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 50.0, 50.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $this->expectException(UnzerApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED);
        $this->unzer->chargeAuthorization($payment->getId(), 70);
    }

    /**
     * Verify the googlepay payment can be charged until it is fully charged and the payment is updated accordingly.
     *
     * @test
     */
    public function partialAndFullChargeAfterAuthorization(): void
    {
        $googlepay          = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay          = $this->unzer->createPaymentType($googlepay);
        $authorization = $googlepay->authorize(100.0000, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment       = $authorization->getPayment();

        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $charge   = $this->unzer->chargeAuthorization($payment->getId(), 20);
        $payment1 = $charge->getPayment();
        $this->assertAmounts($payment1, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment1->isPartlyPaid());

        $charge   = $this->unzer->chargeAuthorization($payment->getId());
        $payment2 = $charge->getPayment();
        $this->assertAmounts($payment2, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment2->isCompleted());
    }

    /**
     * Authorization can be fetched.
     *
     * @test
     */
    public function authorizationShouldBeFetchable(): void
    {
        $googlepay          = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay          = $this->unzer->createPaymentType($googlepay);
        $authorization = $googlepay->authorize(100.0000, 'EUR', self::RETURN_URL);
        $payment       = $authorization->getPayment();

        $fetchedAuthorization = $this->unzer->fetchAuthorization($payment->getId());
        $this->assertEquals($fetchedAuthorization->getId(), $authorization->getId());
    }

    /**
     * @test
     */
    public function fullCancelAfterCharge(): void
    {
        $googlepay    = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay    = $this->unzer->createPaymentType($googlepay);
        $charge  = $googlepay->charge(100.0, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment = $charge->getPayment();

        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $payment->cancelAmount();
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Verify a googlepay payment can be cancelled after being fully charged.
     *
     * @test
     */
    public function fullCancelOnFullyChargedPayment(): void
    {
        $googlepay = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay = $this->unzer->createPaymentType($googlepay);

        $authorization = $googlepay->authorize(100.0000, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment       = $authorization->getPayment();

        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->charge(90.0);
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        $cancellation = $payment->cancelAmount();
        $this->assertNotEmpty($cancellation);
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Full cancel on partly charged auth canceled charges.
     *
     * @test
     */
    public function fullCancelOnPartlyPaidAuthWithCanceledCharges(): void
    {
        $googlepay = $this->createGooglepayObject();
        /** @var Googlepay $googlepay */
        $googlepay = $this->unzer->createPaymentType($googlepay);

        $authorization = $googlepay->authorize(100.0000, 'EUR', self::RETURN_URL, null, null, null, null, false);
        $payment       = $authorization->getPayment();

        $payment->charge(10.0);
        $this->assertAmounts($payment, 90.0, 10.0, 100.0, 0.0);

        $charge = $payment->charge(10.0);
        $this->assertAmounts($payment, 80.0, 20.0, 100.0, 0.0);
        $this->assertTrue($payment->isPartlyPaid());

        $charge->cancel();
        $this->assertAmounts($payment, 80.0, 10.0, 100.0, 10.0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Verify googlepay charge can be canceled.
     *
     * @test
     */
    public function googlepayChargeCanBeCanceled(): void
    {
        /** @var Googlepay $googlepay */
        $googlepay   = $this->unzer->createPaymentType($this->createGooglepayObject());
        $charge = $googlepay->charge(100.0, 'EUR', self::RETURN_URL, null, null, null, null, false);

        $cancel = $charge->cancel();
        $this->assertNotNull($cancel);
        $this->assertNotEmpty($cancel->getId());
    }

    /**
     * Verify googlepay authorize can be canceled.
     *
     * @test
     */
    public function googlepayAuthorizeCanBeCanceled(): void
    {
        /** @var Googlepay $googlepay */
        $googlepay      = $this->unzer->createPaymentType($this->createGooglepayObject());
        $authorize = $googlepay->authorize(100.0, 'EUR', self::RETURN_URL, null, null, null, null, false);

        $cancel = $authorize->cancel();
        $this->assertNotNull($cancel);
        $this->assertNotEmpty($cancel->getId());
    }
}
