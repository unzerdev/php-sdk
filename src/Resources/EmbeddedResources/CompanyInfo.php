<?php
/**
 * Company info class for B2B customer classes.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @package  heidelpayPHP\Resources\EmbeddedResources
 */
namespace heidelpayPHP\Resources\EmbeddedResources;

use heidelpayPHP\Constants\CompanyCommercialSectorItems;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use function is_string;

class CompanyInfo extends AbstractHeidelpayResource
{
    /** @var string $registrationType */
    protected $registrationType;

    /** @var string|null $commercialRegisterNumber */
    protected $commercialRegisterNumber;

    /** @var string|null $function */
    protected $function;

    /** @var string $commercialSector */
    protected $commercialSector = CompanyCommercialSectorItems::OTHER;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getRegistrationType()
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
    public function getCommercialRegisterNumber()
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
    public function getFunction()
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
