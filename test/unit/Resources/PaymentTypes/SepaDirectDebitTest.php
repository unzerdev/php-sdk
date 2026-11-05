<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */

namespace UnzerSDK\test\unit\Resources\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\SepaDirectDebit;
use UnzerSDK\test\BasePaymentTest;
use stdClass;

class SepaDirectDebitTest extends BasePaymentTest
{
    /**
     * Verify constructor sets iban.
     *
     * @test
     */
    public function ibanShouldBeSetByConstructor(): void
    {
        $sdd = new SepaDirectDebit(null);
        $this->assertNull($sdd->getIban());
    }

    /**
     * Verify setter and getter work.
     *
     * @test
     */
    public function getterAndSetterWorkAsExpected(): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');
        $this->assertEquals('DE89370400440532013000', $sdd->getIban());

        $sdd->setIban('DE89370400440532013012');
        $this->assertEquals('DE89370400440532013012', $sdd->getIban());

        $this->assertNull($sdd->getBic());
        $sdd->setBic('RABONL2U');
        $this->assertEquals('RABONL2U', $sdd->getBic());

        $this->assertNull($sdd->getHolder());
        $sdd->setHolder('Max Mustermann');
        $this->assertEquals('Max Mustermann', $sdd->getHolder());
    }

    /**
     * Verify the recurring flag defaults to false and round-trips through the fluent setter.
     *
     * @test
     *
     * @dataProvider recurringValueProvider
     */
    public function recurringSetterAndGetterWorkAsExpected(bool $value): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');
        $this->assertFalse($sdd->isRecurring());

        $this->assertSame($sdd, $sdd->setRecurring($value));
        $this->assertSame($value, $sdd->isRecurring());
    }

    /**
     * Verify the recurring flag is only exposed once explicitly set.
     *
     * @test
     *
     * @dataProvider exposeRecurringProvider
     */
    public function recurringIsExposedOnlyWhenSet(?bool $value, bool $shouldBeExposed): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');
        if ($value !== null) {
            $sdd->setRecurring($value);
        }

        $exposed = $sdd->expose();
        if ($shouldBeExposed) {
            $this->assertArrayHasKey('recurring', $exposed);
            $this->assertSame($value, $exposed['recurring']);
        } else {
            $this->assertArrayNotHasKey('recurring', $exposed);
        }
    }

    /**
     * Verify a response containing the recurring flag is mapped onto the resource.
     *
     * @test
     *
     * @dataProvider recurringValueProvider
     */
    public function recurringIsMappedFromResponse(bool $value): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');

        $response = new stdClass();
        $response->recurring = $value;
        $sdd->handleResponse($response);

        $this->assertSame($value, $sdd->isRecurring());
    }

    /**
     * @return array
     */
    public function recurringValueProvider(): array
    {
        return [
            'recurring true'  => [true],
            'recurring false' => [false],
        ];
    }

    /**
     * @return array
     */
    public function exposeRecurringProvider(): array
    {
        return [
            'set to true'  => [true, true],
            'set to false' => [false, true],
            'not set'      => [null, false],
        ];
    }
}
