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
 *  @author  David Owusu <development@unzer.com>
 *
 *  @package  UnzerSDK
 *
 */

namespace UnzerSDK\Resources;

class Config extends AbstractUnzerResource
{
    /** @var string */
    protected $optinText;

    /**
     * @return string
     */
    public function getOptinText(): ?string
    {
        return $this->optinText;
    }

    /**
     * @param string $optinText
     */
    protected function setOptinText(?string $optinText): void
    {
        $this->optinText = $optinText;
    }
}
