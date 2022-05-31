<?php
/**
 * Creates the different Customer objects.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\Resources
 */
namespace UnzerSDK\Resources;

use UnzerSDK\Constants\CompanyCommercialSectorItems;
use UnzerSDK\Constants\CompanyRegistrationTypes;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;

class CustomerFactory
{
    /**
     * Creates a local Customer object for B2C transactions.
     * Please use Unzer::createCustomer(...) to create the customer resource on the API side.
     *
     * @param string $firstname Firstname is a mandatory for customer.
     * @param string $lastname  Lastname is a mandatory for customer.
     *
     * @return Customer The B2C customer object.
     */
    public static function createCustomer(string $firstname, string $lastname): Customer
    {
        return (new Customer())->setFirstname($firstname)->setLastname($lastname);
    }

    /**
     * Creates a local not registered B2B Customer object for B2C transactions.
     * Please use Unzer::createCustomer(...) to create the customer resource on the API side.
     *
     * @param string  $firstname        Firstname is a mandatory for registered B2B customer.
     * @param string  $lastname         Lastname is a mandatory for registered B2B customer.
     * @param string  $birthDate        Date of birth is a mandatory for registered B2B customer.
     * @param Address $billingAddress   The billing address is mandatory for the registered B2B customer.
     * @param string  $email            The email is mandatory for the registered B2B customer.
     * @param string  $company          The company name is mandatory for the registered B2B customer.
     * @param string  $commercialSector The commercial sector is mandatory for the registered B2B customer.
     *                                  Please refer to CompanyCommercialSectorItems.
     *
     * @return Customer The not registered B2B customer object.
     */
    public static function createNotRegisteredB2bCustomer(
        string $firstname,
        string $lastname,
        string $birthDate,
        Address $billingAddress,
        string $email,
        string $company,
        string $commercialSector = CompanyCommercialSectorItems::OTHER
    ): Customer {
        $companyInfo = (new CompanyInfo())
            ->setRegistrationType(CompanyRegistrationTypes::REGISTRATION_TYPE_NOT_REGISTERED)
            ->setFunction('OWNER')
            ->setCommercialSector($commercialSector);

        return (new Customer())
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setBirthDate($birthDate)
            ->setBillingAddress($billingAddress)
            ->setEmail($email)
            ->setCompany($company)
            ->setCompanyInfo($companyInfo);
    }

    /**
     * @param Address $billingAddress           The billing address is mandatory for the registered B2B customer.
     * @param string  $commercialRegisterNumber The register number of the company.
     * @param string  $company                  The company name is mandatory for the registered B2B customer.
     * @param string  $commercialSector         The commercial sector is not mandatory for the registered B2B customer.
     *                                          Please refer to CompanyCommercialSectorItems.
     *
     * @return Customer
     */
    public static function createRegisteredB2bCustomer(
        Address $billingAddress,
        string $commercialRegisterNumber,
        string $company,
        string $commercialSector = CompanyCommercialSectorItems::OTHER
    ): Customer {
        $companyInfo = (new CompanyInfo())
            ->setRegistrationType(CompanyRegistrationTypes::REGISTRATION_TYPE_REGISTERED)
            ->setFunction('OWNER')
            ->setCommercialRegisterNumber($commercialRegisterNumber)
            ->setCommercialSector($commercialSector);

        return (new Customer())
            ->setCompany($company)
            ->setBillingAddress($billingAddress)
            ->setCompanyInfo($companyInfo);
    }
}
