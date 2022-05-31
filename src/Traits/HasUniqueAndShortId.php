<?php
/**
 * This trait adds the short id and unique id property to a class.
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
 * @package  UnzerSDK\Traits
 */
namespace UnzerSDK\Traits;

trait HasUniqueAndShortId
{
    /** @var string $uniqueId */
    private $uniqueId;

    /** @var string $shortId */
    private $shortId;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    /**
     * @param string $uniqueId
     *
     * @return $this
     */
    protected function setUniqueId(string $uniqueId): self
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShortId(): ?string
    {
        return $this->shortId;
    }

    /**
     * @param string $shortId
     *
     * @return self
     */
    protected function setShortId(string $shortId): self
    {
        $this->shortId = $shortId;
        return $this;
    }

    //</editor-fold>
}
