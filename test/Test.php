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

    public function customerDataProvider()
    {
        return [
            'customer1' => [[
                'birthday' => '2018-08-12',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'company' => 'Musterfirma',
                'state' => 'Bremen',
                'street1' => 'Märchenstraße 3',
                'street2' => 'Hinterhaus',
                'zip' => '12345',
                'city' => 'Pusemuckel',
                'country' => 'Schweiz',
                'email' => 'max@mustermann.de',
                'id' => 'c-123456'
            ]],
            'customer2' => [[
                'birthday' => '2000-01-11',
                'firstname' => 'Linda',
                'lastname' => 'Heideich',
                'company' => 'heidelpay GmbH',
                'street1' => 'Vangerowstr. 18',
                'street2' => 'am Neckar',
                'state' => 'Baden-Würtemberg',
                'zip' => '69115',
                'city' => 'Heidelberg',
                'country' => 'Deutschland',
                'email' => 'lh@heidelpay.de',
                'id' => 'c-654321'
            ]]
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
        $this->expectExceptionMessage('The resources id must be set for this call on API!');
        $customer->fetch();
    }

    /**
     * HeidelpayResource should throw ResourceIdRequiredToFetchResourceException if delete is called without id.
     *
     * @test
     */
    public function heidelpayResourceObjectShouldThrowIdRequiredToDeleteResourceException()
    {
        $customer = new Customer($this->heidelpay);

        $this->expectException(IdRequiredToFetchResourceException::class);
        $this->expectExceptionMessage('The resources id must be set for this call on API!');
        $customer->delete();
    }

    /**
     * Customer should expose private and public properties in array;
     *
     * @dataProvider customerDataProvider
     *
     * @param $expectedData
     *
     * @test
     */
    public function customerObjectShouldExposeItsPrivateAndPublicPropertiesAsArray($expectedData)
    {
        $customer = new Customer();

        foreach ($expectedData as $key=>$item) {
            $setter = 'set' . ucfirst($key);
            $customer->$setter($item);
        }

        $this->assertEquals($expectedData, $customer->expose());
    }
    //</editor-fold>
}
