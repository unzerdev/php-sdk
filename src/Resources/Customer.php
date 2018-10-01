<?php
/**
 * Description
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/${Package}
 */
namespace heidelpay\NmgPhpSdk\Resources;

use heidelpay\NmgPhpSdk\Address;

class Customer extends AbstractHeidelpayResource
{
    /** @var string */
    protected $firstname;

    /** @var string */
    protected $lastname;

    /** @var string $salutation */
    protected $salutation;

    /** @var string $birthDate */
    protected $birthDate;

    /** @var string */
    protected $company;

    /** @var string */
    protected $email;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $mobile;

    /** @var Address $billingAddress */
    protected $billingAddress;

    /** @var string $customerId */
    protected $customerId;

    /**
     * Customer constructor.
     * @param string|null $firstname
     * @param string|null $lastname
     */
    public function __construct(string $firstname = null, string $lastname = null)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->billingAddress = new Address();

        parent::__construct();
    }

    public function getResourcePath(): string
    {
        return 'customers';
    }

    //<editor-fold desc="Getters/Setters">
    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return Customer
     */
    public function setFirstname($firstname): Customer
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return Customer
     */
    public function setLastname($lastname): Customer
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalutation(): string
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     * @return Customer
     */
    public function setSalutation(string $salutation): Customer
    {
        $this->salutation = $salutation;
        return $this;
    }

    /**
     * @return string
     */
    public function getBirthday(): string
    {
        return $this->birthDate;
    }

    /**
     * @param string $birthday
     * @return Customer
     */
    public function setBirthDate($birthday): Customer
    {
        $this->birthDate = $birthday;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return Customer
     */
    public function setCompany($company): Customer
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Customer
     */
    public function setEmail($email): Customer
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Customer
     */
    public function setPhone(string $phone): Customer
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getMobile(): string
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     * @return Customer
     */
    public function setMobile(string $mobile): Customer
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     * @return Customer
     */
    public function setBillingAddress(Address $billingAddress): Customer
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     * @return Customer
     */
    public function setCustomerId(string $customerId): Customer
    {
        $this->customerId = $customerId;
        return $this;
    }
    //</editor-fold>
}