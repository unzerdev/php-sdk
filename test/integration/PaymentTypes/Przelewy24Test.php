<?php
/**
 * This class defines integration tests to verify interface and functionality
 * of the payment method Przelewy24.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class Przelewy24Test extends BasePaymentTest
{
    /**
     * Verify Przelewy24 payment type can be created and fetched.
     *
     * @test
     *
     * @return BasePaymentType
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function przelewy24ShouldBeCreatableAndFetchable(): BasePaymentType
    {
        $przelewy24 = $this->heidelpay->createPaymentType(new Przelewy24());
        $this->assertInstanceOf(Przelewy24::class, $przelewy24);
        $this->assertNotEmpty($przelewy24->getId());

        $fetchedPrzelewy24 = $this->heidelpay->fetchPaymentType($przelewy24->getId());
        $this->assertInstanceOf(Przelewy24::class, $fetchedPrzelewy24);
        $this->assertNotSame($przelewy24, $fetchedPrzelewy24);
        $this->assertEquals($przelewy24->expose(), $fetchedPrzelewy24->expose());

        return $fetchedPrzelewy24;
    }

    /**
     * Verify przelewy24 can authorize.
     *
     * @test
     * @depends przelewy24ShouldBeCreatableAndFetchable
     *
     * @param Przelewy24 $przelewy24
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function przelewy24ShouldBeChargeable(Przelewy24 $przelewy24)
    {
        $charge = $przelewy24->charge(100.0, Currencies::POLISH_ZLOTY, self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());

        $payment = $charge->getPayment();
        $this->assertNotNull($payment);
        $this->assertTrue($payment->isPending());

        $cancel = $charge->cancel();
        $this->assertNotNull($cancel);
        $this->assertNotEmpty($cancel->getId());
    }

    /**
     * Verify przelewy24 can not be authorized.
     *
     * @test
     * @depends przelewy24ShouldBeCreatableAndFetchable
     *
     * @param Przelewy24 $przelewy24
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function przelewy24ShouldNotBeAuthorizable(Przelewy24 $przelewy24)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(100.0, Currencies::POLISH_ZLOTY, $przelewy24, self::RETURN_URL);
    }

    /**
     * Verify przelewy24 can only handle Currency::POLISH_ZLOTY.
     *
     * @test
     *
     * @dataProvider przelewy24CurrencyCodeProvider
     *
     * @param string $currencyCode
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function przelewy24ShouldThrowExceptionIfCurrencyIsNotSupported($currencyCode)
    {
        /** @var Przelewy24 $przelewy24 */
        $przelewy24 = $this->heidelpay->createPaymentType(new Przelewy24());
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CURRENCY_IS_NOT_SUPPORTED);
        $przelewy24->charge(100.0, $currencyCode, self::RETURN_URL);
    }

    //<editor-fold desc="Data Providers">

    /**
     * Provides all defined currencies.
     *
     * @throws \ReflectionException
     */
    public function przelewy24CurrencyCodeProvider(): array
    {
        $currencyArray = $this->currencyCodeProvider();
        unset($currencyArray['POLISH_ZLOTY']);
        return $currencyArray;
    }

    //</editor-fold>
}
