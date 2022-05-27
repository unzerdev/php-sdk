<?php
/**
 * Company owner class for B2B customer.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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

use UnzerSDK\Resources\AbstractUnzerResource;

class CompanyOwner extends AbstractUnzerResource
{
    /** @var string|null $firstname */
    protected $firstname;

    /** @var string|null $lastname */
    protected $lastname;

    /** @var string|null $birthdate */
    protected $birthdate;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     *
     * @return CompanyOwner
     */
    public function setFirstname(?string $firstname): CompanyOwner
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     *
     * @return CompanyOwner
     */
    public function setLastname(?string $lastname): CompanyOwner
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBirthdate(): ?string
    {
        return $this->birthdate;
    }

    /**
     * @param string|null $birthdate
     *
     * @return CompanyOwner
     */
    public function setBirthdate(?string $birthdate): CompanyOwner
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    //</editor-fold>
}
