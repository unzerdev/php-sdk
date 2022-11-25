<?php

/*
 *  This represents the config resource.
 *
 *  Copyright (C) 2021 - today Unzer E-Com GmbH
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *  @link  https://docs.unzer.com/
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\Resources;

use UnzerSDK\Adapter\HttpAdapterInterface;

class Config extends AbstractUnzerResource
{
    /** @var string | null */
    private $dataPrivacyConsent;

    /** @var string | null */
    private $dataPrivacyDeclaration;

    /** @var string | null */
    private $termsAndConditions;

    /** @var array $queryParams Query parameter used for GET request */
    private $queryParams = [];

    private const QUERY_KEY_CUSTOMER_TYPE = 'customerType';
    private const PARAM_KEY_COUNTRY = 'country';

    /**
     * @return string
     */
    public function getDataPrivacyConsent(): ?string
    {
        return $this->dataPrivacyConsent;
    }

    /**
     * @param string $dataPrivacyConsent
     *
     * @return Config
     */
    public function setDataPrivacyConsent(string $dataPrivacyConsent): Config
    {
        $this->dataPrivacyConsent = $dataPrivacyConsent;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataPrivacyDeclaration(): ?string
    {
        return $this->dataPrivacyDeclaration;
    }

    /**
     * @param string $dataPrivacyDeclaration
     *
     * @return Config
     */
    public function setDataPrivacyDeclaration(string $dataPrivacyDeclaration): Config
    {
        $this->dataPrivacyDeclaration = $dataPrivacyDeclaration;
        return $this;
    }

    /**
     * @return string
     */
    public function getTermsAndConditions(): ?string
    {
        return $this->termsAndConditions;
    }

    /**
     * @param string $termsAndConditions
     *
     * @return Config
     */
    public function setTermsAndConditions(string $termsAndConditions): Config
    {
        $this->termsAndConditions = $termsAndConditions;
        return $this;
    }

    /** Get query parameter used for GET request.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /** Set query parameter used for GET request.
     *
     * @param array $queryParams
     *
     * @return Config
     */
    public function setQueryParams(array $queryParams): Config
    {
        $this->queryParams = $queryParams;
        return $this;
    }

    /** Get the 'customerType' value from query parameter array.
     *
     * @return string
     */
    public function getCustomerType(): ?string
    {
        return ($this->queryParams[self::QUERY_KEY_CUSTOMER_TYPE] ?? null);
    }

    /** Set the 'customerType' as query parameter for GET requests.
     *
     * @param string|null $customerType
     *
     * @return Config
     */
    public function setCustomerType(?string $customerType): self
    {
        $this->queryParams[self::QUERY_KEY_CUSTOMER_TYPE] = $customerType;
        return $this;
    }

    /** Get the 'country' value from query parameter array.
     *
     * @return string
     */
    public function getCountry(): ?string
    {
        return ($this->queryParams[self::PARAM_KEY_COUNTRY] ?? null);
    }

    /** Set the 'country' as query parameter for GET requests.
     *
     * @param string|null $country
     *
     * @return Config
     */
    public function setCountry(?string $country): self
    {
        $this->queryParams[self::PARAM_KEY_COUNTRY] = $country;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResourcePath($httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return parent::getResourcePath($httpMethod) . $this->getQueryString();
    }

    /** Return the query string created from 'queryParams'.
     *
     * @return string|null
     */
    private function getQueryString(): ?string
    {
        $query = http_build_query($this->queryParams);
        return $query ? '?' . $query : null;
    }
}
