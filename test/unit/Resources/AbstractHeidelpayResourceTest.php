<?php
/**
 * This class defines unit tests to verify functionality of the AbstractHeidelpayResource.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources;

use heidelpay\MgwPhpSdk\Constants\Salutations;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Address;
use heidelpay\MgwPhpSdk\Resources\Customer;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AbstractHeidelpayResourceTest extends TestCase
{
    /**
     * Verify setter and getter functionality.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function settersAndGettersShouldWork()
    {
        $customer = new Customer();
        $this->assertNull($customer->getId());
        $this->assertNull($customer->getFetchedAt());

        $customer->setId('CustomerId-123');
        $this->assertEquals('CustomerId-123', $customer->getId());

        $customer->setFetchedAt(new \dateTime('2018-12-03'));
        $this->assertEquals(new \dateTime('2018-12-03'), $customer->getFetchedAt());
    }

    /**
     * Verify getParentResource throws exception if it is not set.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     */
    public function getParentResourceShouldThrowExceptionIfItIsNotSet()
    {
        $customer = new Customer();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Parent resource reference is not set!');
        $customer->getParentResource();
    }

    /**
     * Verify getHeidelpayObject calls getParentResource.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function getHeidelpayObjectShouldCallGetParentResourceOnce()
    {
        $customerMock = $this->getMockBuilder(Customer::class)->setMethods(['getParentResource'])->getMock();
        $customerMock->expects($this->once())->method('getParentResource');

        /** @var Customer $customerMock */
        $customerMock->getHeidelpayObject();
    }

    /**
     * Verify getter/setter of ParentResource and Heidelpay object.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     */
    public function parentResourceAndHeidelpayGetterSetterShouldWork()
    {
        $heidelpayObj = new Heidelpay('s-priv-123');
        $customer = new Customer();
        $customer->setParentResource($heidelpayObj);
        $this->assertSame($heidelpayObj, $customer->getParentResource());
        $this->assertSame($heidelpayObj, $customer->getHeidelpayObject());
    }

    /**
     * Verify getUri will call parentResource.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function getUriWillCallGetUriOnItsParentResource()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUri'])
            ->getMock();
        $heidelpayMock->expects($this->once())->method('getUri')->willReturn('parent/resource/path/');

        /** @var Customer $heidelpayMock */
        $customer = (new Customer())->setParentResource($heidelpayMock);
        $this->assertEquals('parent/resource/path/customers/', $customer->getUri());
    }

    /**
     * Verify getUri will return the expected path.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function getUriWillAddIdToTheUriIfItIsSetAndAppendIdIsSet()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUri'])
            ->getMock();
        $heidelpayMock->method('getUri')->willReturn('parent/resource/path/');

        /** @var Customer $heidelpayMock */
        $customer = (new Customer())->setParentResource($heidelpayMock)->setId('myId');
        $this->assertEquals('parent/resource/path/customers/myId/', $customer->getUri());
        $this->assertEquals('parent/resource/path/customers/', $customer->getUri(false));
    }

    /**
     * Verify getUri with appendId == true will append the externalId if it is returned and the id is not set.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     */
    public function getUriWillAddExternalIdToTheUriIfTheIdIsNotSetButAppendIdIs()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUri'])
            ->getMock();
        $heidelpayMock->method('getUri')->willReturn('parent/resource/path/');

        $customerMock = $this->getMockBuilder(Customer::class)->setMethods(['getExternalId'])->getMock();
        $customerMock->expects($this->atLeast(1))->method('getExternalId')->willReturn('myExternalId');

        /** @var Customer $customerMock */
        /** @var Heidelpay $heidelpayMock*/
        $customerMock->setParentResource($heidelpayMock);
        $this->assertEquals('parent/resource/path/customers/myExternalId/', $customerMock->getUri());
        $this->assertEquals('parent/resource/path/customers/', $customerMock->getUri(false));
    }

    /**
     * Verify updateValues will update child objects.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function updateValuesShouldUpdateChildObjects()
    {
        $address = (new Address())
            ->setState('DE-BW')
            ->setCountry('DE')
            ->setName('Max Mustermann')
            ->setCity('Heidelberg')
            ->setZip('69115')
            ->setStreet('Musterstrasse 15');

        $testResponse = new \stdClass();
        $testResponse->billingAddress = json_decode($address->jsonSerialize());

        /** @var Customer $customer */
        $customer = new Customer();
        $customer->handleResponse($testResponse);
        $billingAddress = $customer->getBillingAddress();
        $this->assertEquals('DE-BW', $billingAddress->getState());
        $this->assertEquals('DE', $billingAddress->getCountry());
        $this->assertEquals('Max Mustermann', $billingAddress->getName());
        $this->assertEquals('Heidelberg', $billingAddress->getCity());
        $this->assertEquals('69115', $billingAddress->getZip());
        $this->assertEquals('Musterstrasse 15', $billingAddress->getStreet());
    }

    /**
     * Verify updateValues will update resource fields with values from processing group in response.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function updateValuesShouldUpdateValuesFromProcessingInTheActualObject()
    {
        $testResponse = new \stdClass();
        $processing = new \stdClass();
        $processing->customerId = 'processingCustomerId';
        $processing->firstname = 'processingFirstName';
        $processing->lastname = 'processingLastName';
        $testResponse->processing = $processing;

        /** @var Customer $customer */
        $customer = (new Customer())->setCustomerId('customerId')->setFirstname('firstName')->setLastname('lastName');
        $this->assertEquals('customerId', $customer->getCustomerId());
        $this->assertEquals('firstName', $customer->getFirstname());
        $this->assertEquals('lastName', $customer->getLastname());

        $customer->handleResponse($testResponse);
        $this->assertEquals('processingCustomerId', $customer->getCustomerId());
        $this->assertEquals('processingFirstName', $customer->getFirstname());
        $this->assertEquals('processingLastName', $customer->getLastname());
    }

    /**
     * Verify json_serialize translates a resource in valid json format and values are exposed correctly.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function jsonSerializeShouldTranslateResourceIntoJson()
    {
        $heidelpay =  new Heidelpay('s-priv-123');
        $address = (new Address())
            ->setName('Peter Universum')
            ->setStreet('Hugo-Junkers-Str. 5')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE')
            ->setState('DE-BO');

        $customer = (new Customer())
            ->setCustomerId('CustomerId')
            ->setFirstname('Peter')
            ->setLastname('Universum')
            ->setSalutation(Salutations::MR)
            ->setCompany('heidelpay GmbH')
            ->setBirthDate('1989-12-24')
            ->setEmail('peter.universum@universum-group.de')
            ->setMobile('+49172123456')
            ->setPhone('+4962216471100')
            ->setBillingAddress($address)
            ->setShippingAddress($address)
            ->setParentResource($heidelpay);

        $expectedJson = '{"billingAddress":{"city":"Frankfurt am Main","country":"DE","name":"Peter Universum",' .
            '"state":"DE-BO","street":"Hugo-Junkers-Str. 5","zip":"60386"},"birthDate":"1989-12-24",' .
            '"company":"heidelpay GmbH","customerId":"CustomerId","email":"peter.universum@universum-group.de",' .
            '"firstname":"Peter","lastname":"Universum","mobile":"+49172123456","phone":"+4962216471100",' .
            '"salutation":"mr","shippingAddress":{"city":"Frankfurt am Main","country":"DE","name":"Peter Universum",' .
            '"state":"DE-BO","street":"Hugo-Junkers-Str. 5","zip":"60386"}}';
        $this->assertEquals($expectedJson, $customer->jsonSerialize());
    }
}
