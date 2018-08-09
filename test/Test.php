<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\test;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Customer;
use heidelpay\NmgPhpSdk\Exceptions\HeidelpayObjectMissingException;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\Payment;
use heidelpay\NmgPhpSdk\PaymentTypes\Card;
use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    const KEY = '123456789';
    const RETURN_URL = 'returnURL.php';

    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    //<editor-fold desc="DataProviders">
    public function crudOperationProvider()
    {
        return [
            'Create' => ['create'],
            'Update' => ['update'],
            'Delete' => ['delete'],
            'Fetch' => ['fetch']
        ];
    }
    //</editor-fold>

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::KEY, self::RETURN_URL);
    }

    /**
     * @test
     */
    public function heidelpayObjectShouldHaveGettersAndSettersForProperties()
    {
        $this->assertSame(self::KEY, $this->heidelpay->getKey());
        $this->assertTrue($this->heidelpay->isSandboxMode());
        $this->assertSame(self::RETURN_URL, $this->heidelpay->getReturnUrl());

        $returnUrl = 'newReturnURL.php';
        $key = '987654321';
        $sandboxMode = false;

        $this->heidelpay->setSandboxMode($sandboxMode);
        $this->heidelpay->setKey($key);
        $this->heidelpay->setReturnUrl($returnUrl);

        $this->assertSame($key, $this->heidelpay->getKey());
        $this->assertEquals($sandboxMode, $this->heidelpay->isSandboxMode());
        $this->assertSame($returnUrl, $this->heidelpay->getReturnUrl());
    }

    /**
     * @test
     */
    public function heidelpayObjectShouldCreatePaymentObject()
    {
        $card = new Card('123456789', '09', '2019', '123');
        $card->setHolder('Max Mustermann');

        /** @var Payment $payment */
        $payment = $this->heidelpay->createPayment($card);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($card, $payment->getPaymentType());

        return $card;
    }

    /**
     * @test
     * @param PaymentTypeInterface $card
     * @depends heidelpayObjectShouldCreatePaymentObject
     */
    public function paymentObjectChargeShouldReturnTheUpdatedObject(PaymentTypeInterface $card)
    {
        $this->assertSame($card, $card->charge(12.0, Currency::EUROPEAN_EURO));
        $this->assertSame($card, $card->authorize(12.0, Currency::EUROPEAN_EURO));
    }


    /**
     * HeidelpayResource should throw HeidelpayObjectMissingException if request attempt when the reference is not set.
     *
     * @dataProvider crudOperationProvider
     *
     * @param $crudOperation
     *
     * @test
     */
    public function heidelpayResourceShouldThrowHeidelpayObjectMissingExceptionWhenHeidelpayObjectIsMissingOnCrud(
        $crudOperation
    )
    {
        $customer = new Customer();

        $this->expectException(HeidelpayObjectMissingException::class);
        $this->expectExceptionMessage('Heidelpay object reference is not set!');
        $customer->$crudOperation();
    }

    /**
     * HeidelpayResource should throw ResourceIdRequiredToFetchResourceException if fetch is called without id.
     *
     * @test
     */
    public function heidelpayResourceObjectShouldThrowIdRequiredToFetchResourceException()
    {
        $customer = new Customer($this->heidelpay);

        $this->expectException(IdRequiredToFetchResourceException::class);
        $this->expectExceptionMessage('ResourceId must be set to call fetch on API!');
        $customer->fetch();
    }
    //</editor-fold>
}
