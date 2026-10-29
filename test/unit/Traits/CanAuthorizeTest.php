<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the CanAuthorize trait.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\test\unit\Traits;

use UnzerSDK\Unzer;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\test\BasePaymentTest;
use RuntimeException;

class CanAuthorizeTest extends BasePaymentTest
{
    /**
     * Verify authorize method throws exception if the class does not implement the UnzerParentInterface.
     *
     * @test
     */
    public function authorizeShouldThrowExceptionIfTheClassDoesNotImplementParentInterface(): void
    {
        $dummy = new TraitDummyWithoutCustomerWithoutParentIF();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TraitDummyWithoutCustomerWithoutParentIF');

        $dummy->authorize(1.0, 'MyCurrency', 'https://return.url');
    }

    /**
     * Verify authorize method propagates to Unzer object.
     *
     * @test
     * @dataProvider authorizeDataProvider
     */
    public function authorizeShouldPropagateAuthorizeToUnzer(
        float $amount,
        string $currency,
        string $returnUrl,
        ?object $customer,
        ?string $orderId,
        ?Metadata $metadata
    ): void {
        $unzerMock = $this->getMockBuilder(Unzer::class)
            ->setMethods(['performAuthorization'])
            ->disableOriginalConstructor()
            ->getMock();
        $dummyMock = $this->getMockBuilder(TraitDummyWithoutCustomerWithParentIF::class)
            ->setMethods(['getUnzerObject'])
            ->getMock();

        $expectedAuthorization = new Authorization();
        $dummyMock->method('getUnzerObject')->willReturn($unzerMock);
        $unzerMock->expects($this->once())
            ->method('performAuthorization')
            ->with(
                $this->callback(static function (Authorization $a) use ($amount, $currency, $returnUrl, $orderId) {
                    return $a->getAmount() === $amount
                        && $a->getCurrency() === $currency
                        && $a->getReturnUrl() === $returnUrl
                        && $a->getOrderId() === $orderId;
                }),
                $dummyMock,
                $customer,
                $metadata
            )
            ->willReturn($expectedAuthorization);

        /** @var TraitDummyWithoutCustomerWithParentIF $dummyMock */
        $returnedAuthorize = $dummyMock->authorize($amount, $currency, $returnUrl, $customer, $orderId, $metadata);
        $this->assertSame($expectedAuthorization, $returnedAuthorize);
    }

    public function authorizeDataProvider(): array
    {
        $customer = (new Customer())->setId('123');
        $metadata = new Metadata();
        return [
            'no customer'                => [1.1, 'MyCurrency',  'https://return.url',  null,      null,      null],
            'with customer'              => [1.2, 'MyCurrency2', 'https://return.url2', $customer, null,      null],
            'with customer and orderId'  => [1.3, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', null],
            'with customer and metadata' => [1.4, 'MyCurrency3', 'https://return.url3', $customer, 'orderId', $metadata],
        ];
    }
}
