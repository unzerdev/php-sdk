<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify SCA transactions in general.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\integration\TransactionTypes;

use UnzerSDK\Constants\RecurrenceTypes;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\PaymentTypes\Card;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Sca;
use UnzerSDK\test\BaseIntegrationTest;

class ScaTest extends BaseIntegrationTest
{
    /**
     * Verify SCA transaction can be performed using the id of a payment type.
     *
     * @test
     */
    public function scaShouldWorkWithTypeId(): Sca
    {
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $sca = new Sca(100.0, 'EUR', self::RETURN_URL);
        $sca = $this->unzer->performSca($sca, $paymentType->getId());

        $this->assertTransactionResourceHasBeenCreated($sca);
        $this->assertInstanceOf(Payment::class, $sca->getPayment());
        $this->assertNotEmpty($sca->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $sca->getReturnUrl());

        return $sca;
    }

    /**
     * Verify SCA transaction can be performed with payment type object.
     *
     * @test
     */
    public function scaShouldWorkWithTypeObject(): void
    {
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $sca = new Sca(100.0, 'EUR', self::RETURN_URL);
        $sca = $this->unzer->performSca($sca, $paymentType);

        $this->assertTransactionResourceHasBeenCreated($sca);
        $this->assertInstanceOf(Payment::class, $sca->getPayment());
        $this->assertNotEmpty($sca->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $sca->getReturnUrl());
    }

    /**
     * Verify SCA transaction accepts all parameters.
     *
     * @test
     */
    public function scaShouldAcceptAllParameters(): Sca
    {
        // prepare test data
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $metadata = (new Metadata())->addMetadata('key', 'value');
        $basket = $this->createBasket();
        $paymentReference = 'scaPaymentReference';

        // perform request
        $sca = new Sca(119.0, 'EUR', self::RETURN_URL);
        $sca->setRecurrenceType(RecurrenceTypes::ONE_CLICK, $paymentType);
        $sca->setPaymentReference($paymentReference);
        $sca->setCard3ds(true);

        $sca = $this->unzer->performSca($sca, $paymentType, $customer, $metadata, $basket);

        // verify the data sent and received match
        $payment = $sca->getPayment();
        $this->assertSame($paymentType, $payment->getPaymentType());
        $this->assertEquals(119.0, $sca->getAmount());
        $this->assertEquals('EUR', $sca->getCurrency());
        $this->assertEquals(self::RETURN_URL, $sca->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertEquals($paymentReference, $sca->getPaymentReference());
    }

    /**
     * Verify SCA transaction can be fetched.
     *
     * @test
     * @depends scaShouldWorkWithTypeId
     */
    public function scaTransactionCanBeFetched($sca): void
    {
        $this->assertTransactionResourceHasBeenCreated($sca);

        // Fetch the SCA transaction
        $fetchedSca = $this->unzer->fetchScaById($sca->getPaymentId(), $sca->getId());

        // Verify the fetched transaction matches the initial transaction
        $this->assertEquals($sca->getId(), $fetchedSca->getId());
        $this->assertEquals($sca->getPaymentId(), $fetchedSca->getPaymentId());
        $this->assertEquals($sca->getAmount(), $fetchedSca->getAmount());
        $this->assertEquals($sca->getCurrency(), $fetchedSca->getCurrency());
    }

    /**
     * Verify SCA transaction can be fetched using Sca object.
     *
     * @test
     */
    public function scaTransactionCanBeFetchedUsingSca(): void
    {
        // Create SCA transaction
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $sca = new Sca(100.0, 'EUR', self::RETURN_URL);
        $sca = $this->unzer->performSca($sca, $paymentType);

        $this->assertTransactionResourceHasBeenCreated($sca);

        // Fetch the SCA transaction using the Sca object
        $fetchedSca = $this->unzer->fetchSca($sca);

        // Verify the fetched transaction matches the initial transaction
        $this->assertEquals($sca->getId(), $fetchedSca->getId());
        $this->assertEquals($sca->getAmount(), $fetchedSca->getAmount());
        $this->assertEquals($sca->getCurrency(), $fetchedSca->getCurrency());
    }

    /**
     * Verify charge can be performed on SCA transaction.
     *
     * @test
     */
    public function chargeCanBePerformedOnScaTransaction(): void
    {
        // Create SCA transaction
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $sca = new Sca(100.0, 'EUR', self::RETURN_URL);
        $sca = $this->unzer->performSca($sca, $paymentType);

        $this->assertTransactionResourceHasBeenCreated($sca);

        // Perform charge on SCA transaction
        $charge = $this->unzer->chargeScaTransaction($sca->getPayment(), $sca->getId(), 50.0);

        $this->assertTransactionResourceHasBeenCreated($charge);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertEquals(50.0, $charge->getAmount());
        $this->assertEquals($sca->getPayment()->getId(), $charge->getPayment()->getId());
    }

    /**
     * Verify authorize can be performed on SCA transaction.
     *
     * @test
     */
    public function authorizeCanBePerformedOnScaTransaction(): void
    {
        // Create SCA transaction
        /** @var Card $paymentType */
        $paymentType = $this->unzer->createPaymentType($this->createCardObject());
        $sca = new Sca(100.0, 'EUR', self::RETURN_URL);
        $sca = $this->unzer->performSca($sca, $paymentType);

        $this->assertTransactionResourceHasBeenCreated($sca);

        // Perform authorize on SCA transaction
        $authorization = $this->unzer->authorizeScaTransaction($sca->getPayment(), $sca->getId(), 75.0);

        $this->assertTransactionResourceHasBeenCreated($authorization);
        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertEquals(75.0, $authorization->getAmount());
        $this->assertEquals($sca->getPayment()->getId(), $authorization->getPayment()->getId());
    }
}
