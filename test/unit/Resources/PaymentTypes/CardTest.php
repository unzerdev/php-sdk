<?php
/**
 * This class defines unit tests to verify functionality of Card payment type.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;
use stdClass;

class CardTest extends BaseUnitTest
{
    const TEST_ID = 's-crd-l4bbx7ory1ec';
    const TEST_METHOD_TYPE = 'card';
    const TEST_NUMBER = '444433******1111';
    const TEST_BRAND = 'VISA';
    const TEST_CVC = '***';
    const TEST_EXPIRY_DATE = '03/2020';
    const TEST_HOLDER = 'Max Mustermann';

    private $number     = '4111111111111111';
    private $expiryDate = '12/2030';

    /** @var Card $card */
    private $card;

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function expiryDateDataProvider(): array
    {
        return [
            ['11/22', '11/2022'],
            ['1/12', '01/2012']
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
            ['12/20199'],
            ['0/12']
        ];
    }

    //</editor-fold>

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
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
     * @throws RuntimeException
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
     * @throws RuntimeException
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
     * @throws RuntimeException
     */
    public function yearOfExpiryDateShouldBeExtendedToLongVersion($testData)
    {
        $this->expectException(RuntimeException::class);
        $this->card->setExpiryDate($testData);
    }

    /**
     * Verify setting ExpiryDate null does nothing.
     * This needs to be allowed in order to be able to instantiate the Card without any data to fetch
     * it afterwards by just setting the id.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function verifySettingExpiryDateNullChangesNothing()
    {
        $card = new Card(null, null);
        $this->assertEquals(null, $card->getExpiryDate());

        $this->assertEquals('12/2030', $this->card->getExpiryDate());
        $this->card->setExpiryDate(null);
        $this->assertEquals('12/2030', $this->card->getExpiryDate());
    }

    /**
     * Verify setting cvc.
     *
     * @test
     *
     * @throws Exception
     */
    public function verifyCvcCanBeSetAndChanged()
    {
        $this->assertEquals(null, $this->card->getCvc());
        $this->card->setCvc('123');
        $this->assertEquals('123', $this->card->getCvc());
        $this->card->setCvc('456');
        $this->assertEquals('456', $this->card->getCvc());
    }

    /**
     * Verify setting holder.
     *
     * @test
     *
     * @throws Exception
     */
    public function verifyHolderCanBeSetAndChanged()
    {
        $this->assertEquals(null, $this->card->getHolder());
        $this->card->setHolder('Julia Heideich');
        $this->assertEquals('Julia Heideich', $this->card->getHolder());
        $this->card->setHolder(self::TEST_HOLDER);
        $this->assertEquals(self::TEST_HOLDER, $this->card->getHolder());
    }

    /**
     * Verify card3ds flag.
     *
     * @test
     *
     * @throws AssertionFailedError
     */
    public function card3dsFlagShouldBeSettableInCardResource()
    {
        $this->assertNull($this->card->get3ds());
        $this->card->set3ds(true);
        $this->assertTrue($this->card->get3ds());
        $this->card->set3ds(false);
        $this->assertFalse($this->card->get3ds());
    }

    /**
     * Verify setting brand.
     *
     * @test
     *
     * @throws Exception
     */
    public function verifyCardCanBeUpdated()
    {
        $testResponse = new stdClass();
        $testResponse->id = self::TEST_ID;
        $testResponse->number = self::TEST_NUMBER;
        $testResponse->brand = self::TEST_BRAND;
        $testResponse->cvc = self::TEST_CVC;
        $testResponse->expiryDate = self::TEST_EXPIRY_DATE;
        $testResponse->holder = self::TEST_HOLDER;

        $this->card->handleResponse($testResponse);

        $this->assertEquals(self::TEST_ID, $this->card->getId());
        $this->assertEquals(self::TEST_NUMBER, $this->card->getNumber());
        $this->assertEquals(self::TEST_BRAND, $this->card->getBrand());
        $this->assertEquals(self::TEST_CVC, $this->card->getCvc());
        $this->assertEquals(self::TEST_EXPIRY_DATE, $this->card->getExpiryDate());
        $this->assertEquals(self::TEST_HOLDER, $this->card->getHolder());
    }
}
