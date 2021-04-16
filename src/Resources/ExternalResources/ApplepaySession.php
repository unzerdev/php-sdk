<?php
/*
 *  This class is used for applepay merchant validation request.
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
 *  @author  David Owusu <development@unzer.com>
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\Resources\ExternalResources;

class ApplepaySession
{
    /**
     * This can be found in the Apple Developer Account
     *
     * @var string|null $merchantIdentifier
     */
    private $merchantIdentifier;

    /**
     * This is the Merchant-Name
     *
     * @var string|null $displayName
     */
    private $displayName;

    /**
     * This is the Domain Name which has been validated in the Apple Developer Account.
     *
     * @var string|null $domainName
     */
    private $domainName;

    /**
     * ApplepaySession constructor.
     *
     * @param string $merchantIdentifier
     * @param string $displayName
     * @param string $domainName
     */
    public function __construct(string $merchantIdentifier, string $displayName, string $domainName)
    {
        $this->merchantIdentifier = $merchantIdentifier;
        $this->displayName = $displayName;
        $this->domainName = $domainName;
    }

    /**
     * Returns the json representation of this object's properties.
     *
     * @return false|string
     */
    public function jsonSerialize()
    {
        $properties = get_object_vars($this);
        return json_encode($properties, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * @return string|null
     */
    public function getMerchantIdentifier(): ?string
    {
        return $this->merchantIdentifier;
    }

    /**
     * @param string|null $merchantIdentifier
     *
     * @return ApplepaySession
     */
    public function setMerchantIdentifier(?string $merchantIdentifier): ApplepaySession
    {
        $this->merchantIdentifier = $merchantIdentifier;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     *
     * @return ApplepaySession
     */
    public function setDisplayName(?string $displayName): ApplepaySession
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomainName(): ?string
    {
        return $this->domainName;
    }

    /**
     * @param string|null $domainName
     *
     * @return ApplepaySession
     */
    public function setDomainName(?string $domainName): ApplepaySession
    {
        $this->domainName = $domainName;
        return $this;
    }
}
