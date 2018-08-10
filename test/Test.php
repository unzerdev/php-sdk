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

use heidelpay\NmgPhpSdk\Customer;
use heidelpay\NmgPhpSdk\Exceptions\IdRequiredToFetchResourceException;
use heidelpay\NmgPhpSdk\Exceptions\MissingResourceException;
use heidelpay\NmgPhpSdk\Heidelpay;
use heidelpay\NmgPhpSdk\test\Fixtures\CustomerFixtureTrait;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    use CustomerFixtureTrait;

    const KEY = '123456789';
    const RETURN_URL = 'returnURL.php';

    /** @var Heidelpay $heidelpay */
    private $heidelpay;

    //<editor-fold desc="DataProviders">
    public function crudOperationProvider()
    {
        return [
            'Create' => ['create', self::$customerWithoutId],
            'Update' => ['update', self::$customerWithoutId],
            'Delete' => ['delete', self::$customerWithoutId],
            'Fetch' => ['fetch', self::$customerWithoutId]
        ];
    }

    public function customerDataProvider()
    {
        return [
            'customerA' => [self::$customerW],
            'customerB' => [self::$customerB]
        ];
    }

    //</editor-fold>

    protected function setUp()
    {
        $this->heidelpay = new Heidelpay(self::KEY);
    }

    /**
     * @test
     */
    public function heidelpayObjectShouldHaveGettersAndSettersForProperties()
    {
        $this->assertSame(self::KEY, $this->heidelpay->getKey());
        $this->assertTrue($this->heidelpay->isSandboxMode());

        $key = '987654321';
        $sandboxMode = false;

        $this->heidelpay->setSandboxMode($sandboxMode);
        $this->heidelpay->setKey($key);

        $this->assertSame($key, $this->heidelpay->getKey());
        $this->assertEquals($sandboxMode, $this->heidelpay->isSandboxMode());
    }

    //<editor-fold desc="ResourceObject">

    /**
     * HeidelpayResource should throw ResourceIdRequiredToFetchResourceException if fetch is called without id.
     *
     * @test
     */
    public function heidelpayResourceObjectShouldThrowIdRequiredToFetchResourceException()
    {
        /** @var Customer $customer */
        $customer = $this->heidelpay->createCustomer();

        $this->expectException(IdRequiredToFetchResourceException::class);
        $customer->fetch();
    }

    /**
     * HeidelpayResource should throw ResourceIdRequiredToFetchResourceException if delete is called without id.
     *
     * @test
     */
    public function heidelpayResourceObjectShouldThrowIdRequiredToDeleteResourceException()
    {
        /** @var Customer $customer */
        $customer = $this->heidelpay->createCustomer();

        $this->expectException(IdRequiredToFetchResourceException::class);
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
        /** @var Customer $customer */
        $customer = $this->heidelpay->createCustomer();
        $this->setupCustomer($expectedData, $customer);
        $this->assertEquals($expectedData, $customer->expose());
    }

    /**
     * Customer should be serializable
     *
     * @dataProvider customerDataProvider
     *
     * @param $expectedData
     *
     * @test
     */
    public function customerObjectShouldBeSerializedOnCallingJsonSerialize($expectedData)
    {
        /** @var Customer $customer */
        $customer = $this->heidelpay->createCustomer();
        $this->setupCustomer($expectedData, $customer);
        ksort($expectedData);
        $this->assertEquals(json_encode($expectedData), $customer->jsonSerialize());
    }
    //</editor-fold>

    /**
     * Heidelpay object should throw exception on payment get if the payment object is not set and neither is its id.
     *
     * @test
     */
    public function heidelpayObjectShouldThrowExceptionOnPaymentGetIfObjectAndIdAreNotSet()
    {
        $this->expectException(MissingResourceException::class);
        $this->heidelpay->getPayment();
    }




    //<editor-fold desc="Helper">
    /**
     * @param $expectedData
     * @param $customer
     */
    private function setupCustomer($expectedData, $customer)
    {
        foreach ($expectedData as $key => $item) {
            $setter = 'set' . ucfirst($key);
            $customer->$setter($item);
        }
    }
    //</editor-fold>
}
