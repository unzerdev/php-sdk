<?php
/**
 * This class defines unit tests to verify functionality of the Customer resource.
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
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Constants\Salutations;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\EmbeddedResources\Address;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;

class CustomerTest extends BaseUnitTest
{
    /**
     * Verify setter and getter functionality.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function settersAndGettersShouldWork()
    {
        $customer = new Customer();
        $this->assertNull($customer->getCustomerId());
        $this->assertNull($customer->getFirstname());
        $this->assertNull($customer->getLastname());
        $this->assertNull($customer->getBirthDate());
        $this->assertNull($customer->getPhone());
        $this->assertNull($customer->getMobile());
        $this->assertNull($customer->getEmail());
        $this->assertNull($customer->getCompany());

        $customer->setCustomerId('MyCustomerId-123');
        $this->assertEquals('MyCustomerId-123', $customer->getCustomerId());

        $customer->setFirstname('Peter');
        $this->assertEquals('Peter', $customer->getFirstname());

        $customer->setLastname('Universum');
        $this->assertEquals('Universum', $customer->getLastname());

        $customer->setBirthDate(new \DateTime('1982-11-25'));
        $this->assertEquals(new \DateTime('1982-11-25'), $customer->getBirthDate());

        $customer->setPhone('1234567890');
        $this->assertEquals('1234567890', $customer->getPhone());

        $customer->setMobile('01731234567');
        $this->assertEquals('01731234567', $customer->getMobile());

        $customer->setEmail('peter.universum@universum-group.de');
        $this->assertEquals('peter.universum@universum-group.de', $customer->getEmail());

        $customer->setCompany('heidelpay GmbH');
        $this->assertEquals('heidelpay GmbH', $customer->getCompany());
    }

    /**
     * Verify setter and getter of the billing address.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function settersAndGettersOfBillingAddressShouldWork()
    {
        $address = (new Address())
            ->setState('billing_state')
            ->setCountry('billing_country')
            ->setName('billing_name')
            ->setCity('billing_city')
            ->setZip('billing_zip')
            ->setStreet('billing_street');

        $customer = new Customer();
        $billingAddress = $customer->getBillingAddress();
        $this->assertNull($billingAddress->getState());
        $this->assertNull($billingAddress->getCountry());
        $this->assertNull($billingAddress->getName());
        $this->assertNull($billingAddress->getCity());
        $this->assertNull($billingAddress->getZip());
        $this->assertNull($billingAddress->getStreet());

        $customer->setBillingAddress($address);
        $billingAddress = $customer->getBillingAddress();
        $this->assertEquals('billing_state', $billingAddress->getState());
        $this->assertEquals('billing_country', $billingAddress->getCountry());
        $this->assertEquals('billing_name', $billingAddress->getName());
        $this->assertEquals('billing_city', $billingAddress->getCity());
        $this->assertEquals('billing_zip', $billingAddress->getZip());
        $this->assertEquals('billing_street', $billingAddress->getStreet());
    }

    /**
     * Verify setter and getter of the shipping address.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function settersAndGettersOfShippingAddressShouldWork()
    {
        $address = (new Address())
            ->setState('shipping_state')
            ->setCountry('shipping_country')
            ->setName('shipping_name')
            ->setCity('shipping_city')
            ->setZip('shipping_zip')
            ->setStreet('shipping_street');

        $customer = new Customer();
        $shippingAddress = $customer->getBillingAddress();
        $this->assertNull($shippingAddress->getState());
        $this->assertNull($shippingAddress->getCountry());
        $this->assertNull($shippingAddress->getName());
        $this->assertNull($shippingAddress->getCity());
        $this->assertNull($shippingAddress->getZip());
        $this->assertNull($shippingAddress->getStreet());

        $customer->setShippingAddress($address);
        $shippingAddress = $customer->getShippingAddress();
        $this->assertEquals('shipping_state', $shippingAddress->getState());
        $this->assertEquals('shipping_country', $shippingAddress->getCountry());
        $this->assertEquals('shipping_name', $shippingAddress->getName());
        $this->assertEquals('shipping_city', $shippingAddress->getCity());
        $this->assertEquals('shipping_zip', $shippingAddress->getZip());
        $this->assertEquals('shipping_street', $shippingAddress->getStreet());
    }

    /**
     * Verify salutation only uses the given values.
     *
     * @test
     *
     * @throws Exception
     */
    public function salutationShouldOnlyTakeTheAllowedValues()
    {
        $customer = new Customer();
        $this->assertEquals(Salutations::UNKNOWN, $customer->getSalutation());
        $customer->setSalutation(Salutations::MRS);
        $this->assertEquals(Salutations::MRS, $customer->getSalutation());
        $customer->setSalutation(Salutations::MR);
        $this->assertEquals(Salutations::MR, $customer->getSalutation());
        $customer->setSalutation('MySalutation');
        $this->assertEquals(Salutations::UNKNOWN, $customer->getSalutation());
    }

    /**
     * Verify a Customer is fetched by customerId if the id is not set.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function customerShouldBeFetchedByCustomerIdIfIdIsNotSet()
    {
        $customerId = str_replace(' ', '', microtime());
        $customer = (new Customer())->setParentResource(new Heidelpay('s-priv-123'))->setCustomerId($customerId);
        $lastElement      = explode('/', rtrim($customer->getUri(), '/'));
        $this->assertEquals($customerId, end($lastElement));
    }

    /**
     * Verify fetchCustomerByExtCustomerId method will create a customer object set its customerId and call fetch with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchCustomerByOrderIdShouldCreateCustomerObjectWithCustomerIdAndCallFetch()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(
                static function ($customer) use ($heidelpay) {
                    return $customer instanceof Customer &&
                        $customer->getCustomerId() === 'myCustomerId' &&
                        $customer->getId() === 'myCustomerId' &&
                        $customer->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchCustomerByExtCustomerId('myCustomerId');
    }
}
