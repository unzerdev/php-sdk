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
     * Verify the public setter parameter is named `$recurring`, so the PHP 8 named-argument form works.
     * Since PHP 8 the parameter name is part of the public API; this pins it against accidental renames.
     *
     * @test
     */
    public function recurringSetterAcceptsNamedArgument(): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');

        $sdd->setRecurring(recurring: true);

        $this->assertTrue($sdd->isRecurring());
    }

    /**
     * Verify the recurring flag is always exposed, defaulting to false when never set.
     *
     * @test
     *
     * @dataProvider exposeRecurringProvider
     */
    public function recurringIsAlwaysExposed(?bool $value, bool $expected): void
    {
        $sdd = new SepaDirectDebit('DE89370400440532013000');
        if ($value !== null) {
            $sdd->setRecurring($value);
        }

        $exposed = $sdd->expose();

        $this->assertArrayHasKey('recurring', $exposed);
        $this->assertSame($expected, $exposed['recurring']);
    }

    /**
     * Verify the recurring flag is exposed even when the type is otherwise empty, i.e. the expose()
     * override handles parent::expose() returning an empty stdClass rather than an array.
     *
     * @test
     */
    public function recurringIsExposedWhenTypeIsOtherwiseEmpty(): void
    {
        $sdd = (new SepaDirectDebit(null))->setRecurring(true);

        $exposed = $sdd->expose();

        $this->assertIsArray($exposed);
        $this->assertSame(['recurring' => true], $exposed);
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
            'set to true'           => [true, true],
            'set to false'          => [false, false],
            'not set defaults false' => [null, false],
        ];
    }
}
