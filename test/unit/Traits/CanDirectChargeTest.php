<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the CanDirectCharge trait.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Traits;

use UnzerSDK\Unzer;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BasePaymentTest;
use RuntimeException;

class CanDirectChargeTest extends BasePaymentTest
{
    /**
     * Verify direct charge throws exception if the class does not implement the UnzerParentInterface.
     *
     * @test
     */
    public function directChargeShouldThrowExceptionIfTheClassDoesNotImplementParentInterface(): void
    {
        $dummy = new TraitDummyWithoutCustomerWithoutParentIF();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TraitDummyWithoutCustomerWithoutParentIF');

        $dummy->charge(1.0, 'MyCurrency', 'https://return.url');
    }

    /**
     * Verify direct charge propagates to Unzer object.
     *
     * @test
     * @dataProvider directChargeDataProvider
     */
    public function directChargeShouldPropagateToUnzer(
        float $amount,
        string $currency,
        string $returnUrl,
        ?object $customer,
        ?string $orderId,
        ?Metadata $metadata
    ): void {
        $unzerMock = $this->getMockBuilder(Unzer::class)
            ->setMethods(['performCharge'])
            ->disableOriginalConstructor()
            ->getMock();
        $dummyMock = $this->getMockBuilder(TraitDummyWithoutCustomerWithParentIF::class)
            ->setMethods(['getUnzerObject'])
            ->getMock();

        $expectedCharge = new Charge();
        $dummyMock->method('getUnzerObject')->willReturn($unzerMock);
        $unzerMock->expects($this->once())
            ->method('performCharge')
            ->with(
                $this->callback(static function (Charge $c) use ($amount, $currency, $returnUrl, $orderId) {
                    return $c->getAmount() === $amount
                        && $c->getCurrency() === $currency
                        && $c->getReturnUrl() === $returnUrl
                        && $c->getOrderId() === $orderId;
                }),
                $dummyMock,
                $customer,
                $metadata
            )
            ->willReturn($expectedCharge);

        /** @var TraitDummyWithoutCustomerWithParentIF $dummyMock */
        $returnedCharge = $dummyMock->charge($amount, $currency, $returnUrl, $customer, $orderId, $metadata);
        $this->assertSame($expectedCharge, $returnedCharge);
    }

    public function directChargeDataProvider(): array
    {
        $customer = (new Customer())->setId('123');
        $metadata = new Metadata();
        return [
            'no customer'                  => [1.1, 'MyCurrency',  'https://return.url',  null,      null,      null],
            'with customer'                => [1.2, 'MyCurrency2', 'https://return.url2', $customer, null,      null],
            'with customer and orderId'    => [1.3, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', null],
            'with customer and metadata'   => [1.4, 'MyCurrency4', 'https://return.url4', $customer, 'orderId', $metadata],
        ];
    }
}
