<?php
/**
 * This class defines unit tests to verify functionality of Card payment type.
 *
 * Copyright (C) 2018 Heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources\PaymentTypes;

use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    private $number     = '4111111111111111';
    private $expiryDate = '12/2030';

    /** @var Card $card */
    private $card;

    /**
     * @return array
     */
    public function expiryDateDataProvider(): array
    {
        return [
            ['11/22', '11/2022'],
            ['1/12', '01/2012'],
            ['0/12', '12/2011']
        ];
    }

    /**
     * @return array
     */
    public function invalidExpiryDateDataProvider(): array
    {
        return [
            ['12'],
            ['/12'],
            ['1/1.2'],
            ['asd/12'],
            ['1/asdf'],
            ['13/12'],
            ['12/20199']
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     */
    protected function setUp()
    {
        $this->card = new Card($this->number, $this->expiryDate);
    }

    /**
     * Verify the resource data is set properly.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function constructorShouldSetParameters()
    {
        $number     = '4111111111111111';
        $expiryDate = '12/2030';
        $card       = new Card($number, $expiryDate);

        $this->assertEquals($number, $card->getNumber());
        $this->assertEquals($expiryDate, $card->getExpiryDate());
    }

    /**
     * Verify expiryDate year is extended if it is the short version.
     *
     * @test
     * @dataProvider expiryDateDataProvider
     *
     * @param string $testData
     * @param string $expected
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function expiryDateShouldBeExtendedToLongVersion($testData, $expected)
    {
        $this->card->setExpiryDate($testData);
        $this->assertEquals($expected, $this->card->getExpiryDate());
    }

    /**
     * Verify invalid expiryDate throws Exception.
     *
     * @test
     * @dataProvider invalidExpiryDateDataProvider
     *
     * @param string $testData
     *
     * @throws Exception
     * @throws \RuntimeException
     */
    public function yearOfExpiryDateShouldBeExtendedToLongVersion($testData)
    {
        $this->expectException(\RuntimeException::class);
        $this->card->setExpiryDate($testData);
    }
}
