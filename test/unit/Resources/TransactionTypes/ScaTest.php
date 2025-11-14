<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the SCA transaction type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Resources\TransactionTypes;

use RuntimeException;
use stdClass;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Resources\TransactionTypes\Sca;
use UnzerSDK\test\BasePaymentTest;
use UnzerSDK\Unzer;

class ScaTest extends BasePaymentTest
{

    /**
     * Verify that an SCA can be updated on handle response.
     *
     * @test
     */
    public function aScaShouldBeUpdatedThroughResponseHandling(): void
    {
        $sca = new Sca();
        $this->assertNull($sca->getAmount());
        $this->assertNull($sca->getCurrency());
        $this->assertNull($sca->getReturnUrl());
        $this->assertNull($sca->getIban());
        $this->assertNull($sca->getBic());
        $this->assertNull($sca->getHolder());
        $this->assertNull($sca->getDescriptor());
        $this->assertNull($sca->getRecurrenceType());

        $sca = new Sca(123.4, 'EUR', 'https://my-return-url.test');
        $this->assertEquals(123.4, $sca->getAmount());
        $this->assertEquals('EUR', $sca->getCurrency());
        $this->assertEquals('https://my-return-url.test', $sca->getReturnUrl());

        $testResponse = new stdClass();
        $testResponse->amount = '789.0';
        $testResponse->currency = 'USD';
        $testResponse->returnUrl = 'https://return-url.test';
        $testResponse->Iban = 'DE89370400440532013000';
        $testResponse->Bic = 'COBADEFFXXX';
        $testResponse->Holder = 'Merchant Name';
        $testResponse->Descriptor = '4065.6865.6416';
        $testResponse->additionalTransactionData = (object)['card' => (object)['recurrenceType' => 'oneClick']];

        $sca->handleResponse($testResponse);
        $this->assertEquals(789.0, $sca->getAmount());
        $this->assertEquals('USD', $sca->getCurrency());
        $this->assertEquals('https://return-url.test', $sca->getReturnUrl());
        $this->assertEquals('DE89370400440532013000', $sca->getIban());
        $this->assertEquals('COBADEFFXXX', $sca->getBic());
        $this->assertEquals('Merchant Name', $sca->getHolder());
        $this->assertEquals('4065.6865.6416', $sca->getDescriptor());
    }

    /**
     * Verify response with empty account data can be handled.
     *
     * @test
     */
    public function verifyResponseWithEmptyAccountDataCanBeHandled()
    {
        $sca = new Sca();

        $testResponse = new stdClass();
        $testResponse->Iban = '';
        $testResponse->Bic = '';
        $testResponse->Holder = '';
        $testResponse->Descriptor = '';

        $sca->handleResponse($testResponse);
        $this->assertNull($sca->getIban());
        $this->assertNull($sca->getBic());
        $this->assertNull($sca->getHolder());
        $this->assertNull($sca->getDescriptor());
    }

    /**
     * Verify charge throws exception if payment is not set.
     *
     * @test
     *
     * @dataProvider chargeValueProvider
     *
     * @param float|null $value
     */
    public function chargeShouldThrowExceptionIfPaymentIsNotSet($value): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Payment object is missing. Try fetching the object first!');

        $sca = new Sca();
        $sca->charge($value);
    }

    /**
     * Verify charge() calls chargeScaTransaction() on Unzer object with the given amount.
     *
     * @test
     */
    public function chargeShouldCallChargeScaTransactionOnUnzerObject(): void
    {
        $unzerMock = $this->getMockBuilder(Unzer::class)
            ->disableOriginalConstructor()
            ->setMethods(['chargeScaTransaction'])
            ->getMock();
        /** @var Unzer $unzerMock */
        $payment = (new Payment())->setParentResource($unzerMock)->setId('myPayment');

        $sca = new Sca(100.0, 'EUR', 'https://return-url.test');
        $sca->setPayment($payment);
        $sca->setParentResource($unzerMock);
        $sca->setId('s-sca-123');

        $unzerMock->expects($this->exactly(2))
            ->method('chargeScaTransaction')->willReturn(new Charge())
            ->withConsecutive(
                [$this->identicalTo($payment), 's-sca-123', $this->isNull()],
                [$this->identicalTo($payment), 's-sca-123', 50.0]
            );

        $sca->charge();
        $sca->charge(50.0);
    }

    /**
     * Verify authorize throws exception if payment is not set.
     *
     * @test
     *
     * @dataProvider authorizeValueProvider
     *
     * @param float|null $value
     */
    public function authorizeShouldThrowExceptionIfPaymentIsNotSet($value): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Payment object is missing. Try fetching the object first!');

        $sca = new Sca();
        $sca->authorize($value);
    }

    /**
     * Verify authorize() calls authorizeScaTransaction() on Unzer object with the given amount.
     *
     * @test
     */
    public function authorizeShouldCallAuthorizeScaTransactionOnUnzerObject(): void
    {
        $unzerMock = $this->getMockBuilder(Unzer::class)
            ->disableOriginalConstructor()
            ->setMethods(['authorizeScaTransaction'])
            ->getMock();
        /** @var Unzer $unzerMock */
        $payment = (new Payment())->setParentResource($unzerMock)->setId('myPayment');

        $sca = new Sca(100.0, 'EUR', 'https://return-url.test');
        $sca->setPayment($payment);
        $sca->setParentResource($unzerMock);
        $sca->setId('s-sca-123');

        $unzerMock->expects($this->exactly(2))
            ->method('authorizeScaTransaction')->willReturn(new Authorization())
            ->withConsecutive(
                [$this->identicalTo($payment), 's-sca-123', $this->isNull()],
                [$this->identicalTo($payment), 's-sca-123', 75.0]
            );

        $sca->authorize();
        $sca->authorize(75.0);
    }

    /**
     * Verify getter for cancelled amount.
     *
     * @test
     */
    public function getCancelledAmountReturnsTheCancelledAmount(): void
    {
        $sca = new Sca();
        $this->assertEquals(0.0, $sca->getCancelledAmount());

        $sca = new Sca(123.4, 'EUR', 'https://my-return-url.test');
        $this->assertEquals(0.0, $sca->getCancelledAmount());

        $cancellationJson = '{
            "type": "cancel-sca",
            "status": "success",
            "amount": "10"
        }';

        $cancellation1 = new Cancellation();
        $cancellation1->handleResponse(json_decode($cancellationJson));
        $sca->addCancellation($cancellation1);
        $this->assertEquals(10.0, $sca->getCancelledAmount());

        $cancellation2 = new Cancellation();
        $cancellation2->handleResponse(json_decode($cancellationJson));
        $sca->addCancellation($cancellation2);
        $this->assertEquals(20.0, $sca->getCancelledAmount());
    }

    /**
     * Verify authorizations and charges can be added.
     *
     * @test
     */
    public function authorizationsAndChargesCanBeAdded(): void
    {
        $sca = new Sca(100.0, 'EUR', 'https://return-url.test');

        $this->assertEmpty($sca->getAuthorizations());
        $this->assertEmpty($sca->getCharges());

        $authorization = new Authorization(50.0, 'EUR', 'https://return-url.test');
        $sca->addAuthorization($authorization);

        $charge = new Charge(50.0, 'EUR', 'https://return-url.test');
        $sca->addCharge($charge);

        $this->assertCount(1, $sca->getAuthorizations());
        $this->assertCount(1, $sca->getCharges());
        $this->assertSame($authorization, $sca->getAuthorizations()[0]);
        $this->assertSame($charge, $sca->getCharges()[0]);
    }

    //<editor-fold desc="Data Providers">

    /**
     * Provide different amounts for charge
     *
     * @return array
     */
    public function chargeValueProvider(): array
    {
        return [
            'Amount = null' => [null],
            'Amount = 0.0' => [0.0],
            'Amount = 123.8' => [123.8]
        ];
    }

    /**
     * Provide different amounts for authorize
     *
     * @return array
     */
    public function authorizeValueProvider(): array
    {
        return [
            'Amount = null' => [null],
            'Amount = 0.0' => [0.0],
            'Amount = 123.8' => [123.8]
        ];
    }

    //</editor-fold>
}
