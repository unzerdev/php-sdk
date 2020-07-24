<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\unit
 */
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Resources\EmbeddedResources\CardDetails;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;
use stdClass;

class CardTest extends BasePaymentTest
{
    private const TEST_ID = 's-crd-l4bbx7ory1ec';
    private const TEST_NUMBER = '444433******1111';
    private const TEST_BRAND = 'VISA';
    private const TEST_CVC = '***';
    private const TEST_EXPIRY_DATE = '03/2020';
    private const TEST_HOLDER = 'Max Mustermann';

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
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->card = new Card($this->number, $this->expiryDate);
    }

    /**
     * Verify the resource data is set properly.
     *
     * @test
     */
    public function constructorShouldSetParameters(): void
    {
        $number     = '4111111111111111';
        $expiryDate = '12/2030';
        $card       = new Card($number, $expiryDate);

        $this->assertEquals($number, $card->getNumber());
        $this->assertEquals($expiryDate, $card->getExpiryDate());

        $geoLocation = $card->getGeoLocation();
        $this->assertNull($geoLocation->getClientIp());
        $this->assertNull($geoLocation->getCountryCode());
    }

    /**
     * Verify expiryDate year is extended if it is the short version.
     *
     * @test
     * @dataProvider expiryDateDataProvider
     *
     * @param string $testData
     * @param string $expected
     */
    public function expiryDateShouldBeExtendedToLongVersion($testData, $expected): void
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
     */
    public function yearOfExpiryDateShouldBeExtendedToLongVersion($testData): void
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
     */
    public function verifySettingExpiryDateNullChangesNothing(): void
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
     */
    public function verifyCvcCanBeSetAndChanged(): void
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
     */
    public function verifyHolderCanBeSetAndChanged(): void
    {
        $this->assertEquals(null, $this->card->getCardHolder());
        $this->card->setCardHolder('Julia Heideich');
        $this->assertEquals('Julia Heideich', $this->card->getCardHolder());
        $this->card->setCardHolder(self::TEST_HOLDER);
        $this->assertEquals(self::TEST_HOLDER, $this->card->getCardHolder());
    }

    /**
     * Verify setting holder.
     *
     * @test
     *
     * @deprecated since 1.2.7.2
     */
    public function verifyHolderCanBeSetAndChangedOld(): void
    {
        $this->assertEquals(null, $this->card->getHolder());
        $this->card->setHolder('Julia Heideich');
        $this->assertEquals('Julia Heideich', $this->card->getHolder());
        $this->card->setHolder(self::TEST_HOLDER);
        $this->assertEquals(self::TEST_HOLDER, $this->card->getHolder());
    }

    /**
     * Verify setting holder.
     *
     * @test
     *
     * @deprecated since 1.2.7.2
     */
    public function verifyHolderSettersPropagate(): void
    {
        $cardMock = $this->getMockBuilder(Card::class)->disableOriginalConstructor()->setMethods(['setCardHolder', 'getCardHolder'])->getMock();
        /** @noinspection PhpParamsInspection */
        $cardMock->expects($this->once())->method('setCardHolder')->with('set my CardHolder');
        $cardMock->expects($this->once())->method('getCardHolder')->willReturn('get my CardHolder');

        /** @var Card $cardMock */
        $cardMock->setHolder('set my CardHolder');
        $this->assertSame('get my CardHolder', $cardMock->getHolder());
    }

    /**
     * Verify card3ds flag.
     *
     * @test
     */
    public function card3dsFlagShouldBeSettableInCardResource(): void
    {
        $this->assertNull($this->card->get3ds());
        $this->assertArrayNotHasKey('3ds', $this->card->expose());
        $this->assertArrayNotHasKey('card3ds', $this->card->expose());

        $this->card->set3ds(true);
        $this->assertTrue($this->card->get3ds());
        $this->assertTrue($this->card->expose()['3ds']);
        $this->assertArrayNotHasKey('card3ds', $this->card->expose());

        $this->card->set3ds(false);
        $this->assertFalse($this->card->get3ds());
        $this->assertFalse($this->card->expose()['3ds']);
        $this->assertArrayNotHasKey('card3ds', $this->card->expose());
    }

    /**
     * Verify setting brand.
     *
     * @test
     */
    public function verifyCardCanBeUpdated(): void
    {
        $newGeoLocation = (object)['clientIp' => 'client ip', 'countryCode' => 'country code'];
        $newValues = (object)[
            'id' => self::TEST_ID,
            'number' => self::TEST_NUMBER,
            'brand' => self::TEST_BRAND,
            'cvc' => self::TEST_CVC,
            'expiryDate' => self::TEST_EXPIRY_DATE,
            'cardHolder' => self::TEST_HOLDER,
            'geolocation' => $newGeoLocation
        ];

        $this->card->handleResponse($newValues);

        $this->assertEquals(self::TEST_ID, $this->card->getId());
        $this->assertEquals(self::TEST_NUMBER, $this->card->getNumber());
        $this->assertEquals(self::TEST_BRAND, $this->card->getBrand());
        $this->assertEquals(self::TEST_CVC, $this->card->getCvc());
        $this->assertEquals(self::TEST_EXPIRY_DATE, $this->card->getExpiryDate());
        $this->assertEquals(self::TEST_HOLDER, $this->card->getCardHolder());
        $cardDetails = $this->card->getCardDetails();
        $this->assertNull($cardDetails);

        $geoLocation = $this->card->getGeoLocation();
        $this->assertEquals('client ip', $geoLocation->getClientIp());
        $this->assertEquals('country code', $geoLocation->getCountryCode());

        $cardDetails = new stdClass;
        $cardDetails->cardType = 'my card type';
        $cardDetails->account = 'CREDIT';
        $cardDetails->countryIsoA2 = 'DE';
        $cardDetails->countryName = 'Germany';
        $cardDetails->issuerName = 'my issuer name';
        $cardDetails->issuerUrl = 'https://my.issuer.url';
        $cardDetails->issuerPhoneNumber = '+49 6221 6471-400';
        $newValues->cardDetails = $cardDetails;

        $this->card->handleResponse($newValues);
        $this->assertEquals(self::TEST_ID, $this->card->getId());
        $this->assertEquals(self::TEST_NUMBER, $this->card->getNumber());
        $this->assertEquals(self::TEST_BRAND, $this->card->getBrand());
        $this->assertEquals(self::TEST_CVC, $this->card->getCvc());
        $this->assertEquals(self::TEST_EXPIRY_DATE, $this->card->getExpiryDate());
        $this->assertEquals(self::TEST_HOLDER, $this->card->getCardHolder());
        $details = $this->card->getCardDetails();
        $this->assertInstanceOf(CardDetails::class, $details);
        $this->assertEquals('my card type', $details->getCardType());
        $this->assertEquals('CREDIT', $details->getAccount());
        $this->assertEquals('DE', $details->getCountryIsoA2());
        $this->assertEquals('Germany', $details->getCountryName());
        $this->assertEquals('my issuer name', $details->getIssuerName());
        $this->assertEquals('https://my.issuer.url', $details->getIssuerUrl());
        $this->assertEquals('+49 6221 6471-400', $details->getIssuerPhoneNumber());
    }
}
