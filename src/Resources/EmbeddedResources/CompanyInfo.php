<?php
/**
 * Company info class for B2B customer classes.
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
 * @package  UnzerSDK\Resources\EmbeddedResources
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Constants\CompanyCommercialSectorItems;
use UnzerSDK\Resources\AbstractUnzerResource;
use stdClass;
use function is_string;

class CompanyInfo extends AbstractUnzerResource
{
    /** @var string $registrationType */
    protected $registrationType;

    /** @var string|null $commercialRegisterNumber */
    protected $commercialRegisterNumber;

    /** @var string|null $function */
    protected $function;

    /** @var string $commercialSector */
    protected $commercialSector = CompanyCommercialSectorItems::OTHER;

    /** @var string|null $companyType */
    protected $companyType;

    /** @var CompanyOwner|null $owner */
    protected $owner;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getRegistrationType(): ?string
    {
        return $this->registrationType;
    }

    /**
     * @param string|null $registrationType
     *
     * @return CompanyInfo
     */
    public function setRegistrationType($registrationType): CompanyInfo
    {
        $this->registrationType = $this->removeRestrictedSymbols($registrationType);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommercialRegisterNumber(): ?string
    {
        return $this->commercialRegisterNumber;
    }

    /**
     * @param string|null $commercialRegisterNumber
     *
     * @return CompanyInfo
     */
    public function setCommercialRegisterNumber($commercialRegisterNumber): CompanyInfo
    {
        $this->commercialRegisterNumber = empty($commercialRegisterNumber) ?
            $commercialRegisterNumber : $this->removeRestrictedSymbols($commercialRegisterNumber);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFunction(): ?string
    {
        return $this->function;
    }

    /**
     * @param string|null $function
     *
     * @return CompanyInfo
     */
    public function setFunction($function): CompanyInfo
    {
        $this->function = $this->removeRestrictedSymbols($function);
        return $this;
    }

    /**
     * @return string
     */
    public function getCommercialSector(): string
    {
        return $this->commercialSector;
    }

    /**
     * @param string $commercialSector
     *
     * @return CompanyInfo
     */
    public function setCommercialSector(string $commercialSector): CompanyInfo
    {
        $this->commercialSector = $this->removeRestrictedSymbols($commercialSector);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    /**
     * @param string|null $companyType
     *
     * @return CompanyInfo
     */
    public function setCompanyType(?string $companyType): CompanyInfo
    {
        $this->companyType = $companyType;
        return $this;
    }

    /**
     * @return CompanyOwner|null
     */
    public function getOwner(): ?CompanyOwner
    {
        return $this->owner;
    }

    /**
     * @param CompanyOwner|null $owner
     *
     * @return CompanyInfo
     */
    public function setOwner(?CompanyOwner $owner): CompanyInfo
    {
        $this->owner = $owner;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    /**
     * Create instances of necessary properties to handle API responses.
     *
     * @param stdClass $response
     *
     * @return void
     */
    public function instantiateObjectsFromResponse(stdClass $response): void
    {
        if (isset($response->owner) && $this->owner === null) {
            $this->owner = new CompanyOwner();
        }
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * Removes some restricted symbols from the given value.
     *
     * @param string|null $value
     *
     * @return mixed
     */
    private function removeRestrictedSymbols($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return str_replace(['<', '>'], '', $value);
    }

    //</editor-fold>
}
